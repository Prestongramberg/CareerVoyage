import React from "react"
import { connect } from "react-redux"
import { loadIndustries, primaryIndustryChanged, subscribe, unsubscribe, unsubscribeAll } from './actions/actionCreators'
import PropTypes from "prop-types";
import { getSecondaryIndustry } from "./helpers/industries"
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["relevantSecondaryIndustries", "renderFields", "subscribeToAllSecondaryIndustries"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        return this.props.uiState.loading ? (
            <div className="uk-width-1-1 uk-align-center">
                <Loader />
            </div>
        ) : this.renderFields();
    }

    renderFields() {

        const secondaryIndustries = this.relevantSecondaryIndustries();
        const currentPrimaryIndustry = !!this.props.uiState.primaryIndustrySelected ? this.props.subscriptions.data.find( event => event.id === this.props.uiState.primaryIndustrySelected ) : {};

        if ( this.props.subscriptions.data.length === 0 ) {
            return <p>Something went wrong, please contact support.</p>
        }

        return (
            <div>

                <div className="uk-section uk-section-muted uk-padding">
                    <div className="uk-grid" data-uk-grid>
                        <div className="uk-width-1-1">
                            <h4>{ this.props.currentTitle || "Add Relevant Career Fields" }</h4>
                            <p>Start by selecting an industry.  Relevant career fields are then shown and can be added individually (or use select all if applicable).  If you are interested in multiple industries, select another option from the main dropdown.</p>
                            <div className="uk-grid" data-uk-grid>
                                <div className="uk-width-1-2">
                                    <select className="uk-select" onChange={this.props.primaryIndustryChanged}>
                                        <option value="">Select an Industry</option>
                                        {this.props.subscriptions.data.map(industry => <option key={industry.id} value={industry.id}>{industry.name}</option>)}
                                    </select>
                                </div>

                                { !!this.props.uiState.primaryIndustrySelected && (
                                    <div className="uk-width-1-2">
                                        <select className="uk-select" onChange={this.props.secondaryIndustryChanged} value={this.props.uiState.secondaryIndustrySelected}>
                                            <option value="">Add Specific Career Field</option>
                                            {secondaryIndustries.map(industry => <option key={industry.id} value={industry.id}>{industry.name}</option>)}
                                        </select>

                                        { currentPrimaryIndustry && currentPrimaryIndustry.name && (
                                            <div className="uk-text-center">
                                                <hr/>
                                                <strong>OR</strong>
                                                <hr/>
                                                <a onClick={this.subscribeToAllSecondaryIndustries}>Add all Career Fields</a>
                                            </div>
                                        )}
                                    </div>
                                ) }
                            </div>
                        </div>
                    </div>
                </div>

                {this.props.subscriptions.subscribed.length > 0 && (
                    <div className="uk-margin">
                        <div className="uk-grid" data-uk-grid>
                            <div className="uk-width-expand">
                                <h4>{ this.props.existingTitle || "Current Career Fields:" } </h4>
                            </div>
                            <div className="uk-width-auto">
                                <a onClick={this.props.removeAllSubscriptions}><em>Remove All</em></a>
                            </div>
                        </div>
                        <ul className="uk-list uk-list-divider">
                            {this.props.subscriptions.subscribed.map((secondaryIndustryId, index) => {
                                const secondaryIndustry = getSecondaryIndustry(this.props.subscriptions.data, secondaryIndustryId);
                                if ( secondaryIndustry !== null ) {
                                    return <li key={secondaryIndustry.id}>
                                        <div className="uk-grid uk-flex-middle uk-margin-remove-vertical" data-uk-grid>
                                            <div className="uk-width-expand">
                                                <span>{ secondaryIndustry.name }</span>
                                                <input type="hidden" name={`${this.props.fieldName}[${index}]`} value={secondaryIndustry.id} />
                                            </div>
                                            <div className="uk-width-auto">
                                                <button type="button"
                                                        onClick={() => {
                                                            this.props.removeIndustry( secondaryIndustry.id );
                                                        }}
                                                        data-uk-close></button>
                                            </div>
                                        </div>
                                    </li>

                                }
                                return null;
                            })}
                        </ul>
                    </div>
                )}
            </div>
        )
    }

    relevantSecondaryIndustries() {
        const secondaryIndustries = [];

        if ( !this.props.uiState.loading && this.props.uiState.primaryIndustrySelected ) {
            const primaryIndustry = this.props.subscriptions.data.find(industry => industry.id === this.props.uiState.primaryIndustrySelected);
            return primaryIndustry ? primaryIndustry.secondaryIndustries : [];
        }

        return secondaryIndustries;
    }

    subscribeToAllSecondaryIndustries() {
        const currentPrimaryIndustry = !!this.props.uiState.primaryIndustrySelected ? this.props.subscriptions.data.find( event => event.id === this.props.uiState.primaryIndustrySelected ) : {};

        if ( currentPrimaryIndustry && currentPrimaryIndustry.secondaryIndustries ) {
            currentPrimaryIndustry.secondaryIndustries.forEach(secondaryIndustry => {
                this.props.secondaryIndustryChanged({
                    target: {
                        value: secondaryIndustry.id
                    }
                });
            });
        }
    }

    componentDidMount() {
        this.props.loadIndustries( window.Routing.generate('get_industries'), this.props.removeDomId );
    }
}

App.propTypes = {
    currentTitle: PropTypes.string,
    existingTitle: PropTypes.string,
    fieldName: PropTypes.string,
    initialIndustrySubscriptions: PropTypes.array,
    removeDomId: PropTypes.string,
    subscriptions: PropTypes.object,
    uiState: PropTypes.object,
};

App.defaultProps = {
    subscriptions: {},
    uiState: {},
};

export const mapStateToProps = (state = {}) => ({
    subscriptions: state.subscriptions,
    uiState: state.uiState
});

export const mapDispatchToProps = dispatch => ({
    loadIndustries: (url, removeDomId) => dispatch(loadIndustries(url, removeDomId)),
    primaryIndustryChanged: (event) => dispatch(primaryIndustryChanged(event.target.value)),
    secondaryIndustryChanged: (event) => dispatch(subscribe(event.target.value)),
    removeIndustry: (industryId) => dispatch(unsubscribe(industryId)),
    removeAllSubscriptions: () => dispatch(unsubscribeAll())
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
