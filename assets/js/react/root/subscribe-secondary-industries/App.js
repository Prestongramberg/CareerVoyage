import React from "react"
import { connect } from "react-redux"
import { loadIndustries, loadSecondaryIndustries, primaryIndustryChanged, secondaryIndustrySearched, subscribe, unsubscribe, unsubscribeAll } from './actions/actionCreators'
import PropTypes from "prop-types";
import { getSecondaryIndustry } from "./helpers/industries"
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["relevantSecondaryIndustries", "renderFields", "subscribeToAllSecondaryIndustries", "subscribeToAllIndustries"];
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
        const allSecondaryIndustries = this.allSecondaryIndustries();
        const currentPrimaryIndustry = !!this.props.uiState.primaryIndustrySelected ? this.props.subscriptions.data.find( event => event.id === this.props.uiState.primaryIndustrySelected ) : {};

        let filteredIndustries = allSecondaryIndustries.filter(
            (secondaryIndustry) => {
                return secondaryIndustry.name.toLowerCase().indexOf(this.state.search.toLowerCase()) !== -1;
            }
        );

        if ( this.props.subscriptions.data.length === 0 ) {
            return <p>Something went wrong, please contact support.</p>
        }

        return (
            <div>

                <div className="uk-section uk-section-muted uk-padding">
                    <div className="uk-grid" data-uk-grid>
                        <div className="uk-width-1-1">
                            <h4>{ this.props.currentTitle || "Add Relevant Career Fields" }</h4>
                            { this.props.userKind == "educator" && (
                                <p>Add Relevant Career Fields that you or your students are interested in or relate to the subjects you teach. Then you can be notified when experiences related to those career fields are posted and professionals from those careers will be able to reach out to you.</p>
                            )}

                            { this.props.userKind == "student" && (
                                <p>Add Relevant Career Fields that you are interested in so that you can be notified when experiences related to those career fields are posted.</p>
                            )}
                            
                            <p>Start by either searching for a career or selecting an industry that you are interested in. Then select specific career fields in that industry or use “Add all career Fields” if applicable. Then, if you wish to select multiple industries, select the next industry from the industry dropdown menu and repeat the process of adding career fields.</p>
                            
                            <div className="uk-grid" data-uk-grid>
                                <div className="uk-width-1-1">
                                    {/* TODO - this needs to be fixed to allow searching from this field. */}
                                    <input type="text" placeholder="Search for Career Field" className="uk-input" value={this.state.search} onChange={this.props.secondaryIndustrySearched} />
                                    <div uk-dropdown="mode: click; pos: bottom-justify">
                                        <ul>
                                            {/* { this.props.subscriptions.data.map(industry => <li key={industry.id} data-value={industry.id}>{ industry.name }</li>) } */}
                                            { filteredIndustries.map( industry => <li key={industry.id} data-value={industry.id}>{ industry.name }</li> )}
                                        </ul>
                                    </div>

                                    <p className="uk-text-center"><strong>OR</strong></p>
                                </div>
                            </div>
                            <div className="uk-grid" data-uk-grid>
                                <div className="uk-width-1-2">
                                    <select className="uk-select" onChange={this.props.primaryIndustryChanged}>
                                        <option value="">Select an Industry</option>
                                        {this.props.subscriptions.data.map(industry => <option key={industry.id} value={industry.id}>{industry.name}</option>)}
                                    </select>
                                    <div className="uk-text-center">
                                        <hr/>
                                        <strong>OR</strong>
                                        <hr/>
                                        <a onClick={this.subscribeToAllIndustries}>Add all Industry Careers</a>
                                    </div>
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
                              {/* Updated This Area */}
                            {/* <button className="uk-button uk-button-primary uk-button-small">Next</button> */}
                          </div>
                        </div>
                        <div className="uk-grid" data-uk-grid>
                            <div className="uk-width-expand">
                                <h4>{ this.props.existingTitle || "Applicable Career Fields:" } </h4>
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

    allSecondaryIndustries() {
        const secondaryIndustries = [];

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

    subscribeToAllIndustries() {
        this.props.subscriptions.data.forEach(primaryIndustry => {
            primaryIndustry.secondaryIndustries.forEach(secondaryIndustry => {
                this.props.secondaryIndustryChanged({
                    target: {
                        value: secondaryIndustry.id
                    }
                });
            });
        });
    }

    componentDidMount() {
        this.props.loadIndustries( window.Routing.generate('get_industries'), this.props.removeDomId );
        this.props.loadSecondaryIndustries( window.Routing.generate('get_secondary_industries'), this.props.removeDomId );
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
    userKind: PropTypes.string,
    search: PropTypes.string
};

App.defaultProps = {
    subscriptions: {},
    uiState: {},
    search: 'RDS'
};

export const mapStateToProps = (state = {}) => ({
    subscriptions: state.subscriptions,
    uiState: state.uiState,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({
    loadIndustries: (url, removeDomId) => dispatch(loadIndustries(url, removeDomId)),
    loadSecondaryIndustries: (url, removeDomId) => dispatch(loadSecondaryIndustries(url, removeDomId)),
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
