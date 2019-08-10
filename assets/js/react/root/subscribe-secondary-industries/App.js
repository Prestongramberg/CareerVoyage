import React from "react"
import { connect } from "react-redux"
import { loadIndustries, primaryIndustryChanged, subscribe, unsubscribe } from './actions/actionCreators'
import PropTypes from "prop-types";
import { getSecondaryIndustry } from "./helpers/industries"

class App extends React.Component {

    constructor() {
        super();
        const methods = ["relevantSecondaryIndustries", "renderFields"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        return this.props.uiState.loading ? (
            <div className="uk-width-1-1 uk-align-center">
                <div data-uk-spinner></div>
            </div>
        ) : this.renderFields();
    }

    renderFields() {

        const secondaryIndustries = this.relevantSecondaryIndustries();

        if ( this.props.subscriptions.data.length === 0 ) {
            return <p>Something went wrong, please contact support.</p>
        }

        return (
            <div>

                <div className="uk-section uk-section-muted uk-padding">
                    <div className="uk-grid" data-uk-grid>
                        <div className="uk-width-1-1">
                            <h4>Subscribe to Career Fields</h4>
                            <div className="uk-grid" data-uk-grid>
                                <div className="uk-width-1-2">
                                    <select className="uk-select" onChange={this.props.primaryIndustryChanged}>
                                        <option value="">Select an Industry</option>
                                        {this.props.subscriptions.data.map(industry => <option key={industry.id} value={industry.id}>{industry.name}</option>)}
                                    </select>
                                </div>

                                { this.props.uiState.primaryIndustrySelected && (
                                    <div className="uk-width-1-2">
                                        <select className="uk-select" onChange={this.props.secondaryIndustryChanged} value={this.props.uiState.secondaryIndustrySelected}>
                                            <option value="">Add A Career Field</option>
                                            {secondaryIndustries.map(industry => <option key={industry.id} value={industry.id}>{industry.name}</option>)}
                                        </select>
                                    </div>
                                ) }
                            </div>
                        </div>
                    </div>
                </div>

                {this.props.subscriptions.subscribed.length > 0 && (
                    <div className="uk-margin">
                        <h4>Currently Subscribed Career Fields:</h4>
                        <ul className="uk-list uk-list-divider">
                            {this.props.subscriptions.subscribed.map(secondaryIndustryId => {
                                const secondaryIndustry = getSecondaryIndustry(this.props.subscriptions.data, secondaryIndustryId);
                                if ( secondaryIndustry !== null ) {
                                    return <li>
                                        <div className="uk-grid uk-flex-middle uk-margin-remove-vertical" uk-grid>
                                            <div className="uk-width-expand">
                                                <p>{ secondaryIndustry.name }</p>
                                            </div>
                                            <div className="uk-width-auto">
                                                <button type="button"
                                                        data-remove={''}
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

    componentDidMount() {
        this.props.loadIndustries( window.Routing.generate('get_industries'), this.props.initialIndustrySubscriptions );
    }
}

App.propTypes = {
    initialIndustrySubscriptions: PropTypes.array,
    subscriptions: PropTypes.object,
    subscribeEndpoint: PropTypes.func,
    unSubscribeEndpoint: PropTypes.func,
    uiState: PropTypes.object,
};

App.defaultProps = {
    initialIndustrySubscriptions: [],
    subscriptions: {},
    subscribeEndpoint: () => {},
    unSubscribeEndpoint: () => {},
    uiState: {},
};

export const mapStateToProps = (state = {}) => ({
    subscriptions: state.subscriptions,
    uiState: state.uiState
});

export const mapDispatchToProps = dispatch => ({
    loadIndustries: (url, subscribed) => dispatch(loadIndustries(url, subscribed)),
    primaryIndustryChanged: (event) => dispatch(primaryIndustryChanged(event.target.value)),
    secondaryIndustryChanged: (event) => dispatch(subscribe(event.target.value)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;