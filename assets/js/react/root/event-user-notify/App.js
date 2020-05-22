import React from "react"
import { connect } from "react-redux"
import { onNotifyButtonClick, onFormSubmit, closeModal, onSelectFieldChange, onTextareaFieldChange, updateCompanyQuery, updatePrimaryIndustryQuery, updateSecondaryIndustryQuery } from './actions/actionCreators'
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["renderCompanyDropdown", "renderIndustryDropdown", "renderSecondaryIndustryDropdown", "getRelevantUsers"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {

        const uniqueId = this.props.uniqueId || Math.round(Math.random() * 100000);

        return (
            <div>
                <button data-uk-toggle={`target: #notify-professionals-of-event-${uniqueId}`} type="button"
                        className="uk-button uk-button-danger uk-button-small alert alert-primary">
                    {this.props.ui.title}
                </button>
                <div id={`notify-professionals-of-event-${uniqueId}`} className="uk-modal-800" data-uk-modal>
                    <div className="uk-modal-dialog uk-modal-body">
                        <h2>{this.props.ui.title}</h2>
                        <br />

                        <div className="uk-grid-small uk-flex-middle uk-grid" data-uk-grid>
                            { this.renderCompanyDropdown() }
                            { this.renderIndustryDropdown() }
                            { this.renderSecondaryIndustryDropdown() }
                        </div>

                        <form className="form-inline uk-margin" onSubmit={(event) => { this.props.onFormSubmit(event, window.Routing.generate('experience_notify_users', {id: this.props.experienceId})) }}>

                            <div className="user-listings">

                                { (this.props.ui.loading) && (
                                    <div className="uk-width-1-1 uk-align-center">
                                        <Loader />
                                    </div>
                                )}

                                {this.getRelevantUsers().length > 0 ? (

                                    <select name="users[]" className="uk-select" multiple="multiple" onChange={this.props.onSelectFieldChange}>
                                        {
                                            this.getRelevantUsers().map(user => (
                                                <option value={user.id}>{user.firstName} {user.lastName}</option>
                                            ))
                                        }
                                    </select>
                                ) : (
                                    <p>No users were found with this criteria.</p>
                                )
                                }
                            </div>

                            <div className="customMessage uk-margin">
                                <label>Custom Notification Message</label>
                                <textarea className="uk-textarea" name="customMessage" onChange={this.props.onTextareaFieldChange}></textarea>
                            </div>

                            <div className="uk-margin">
                                <button type="submit" className="uk-button uk-button-primary uk-button-small uk-display-inline-block">Send Notification</button>
                                <button className="uk-button uk-button-default uk-modal-close uk-button-small uk-display-inline-block uk-margin-left">Cancel</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        )
    }

    getRelevantUsers () {

        return this.props.users.filter(user => {

            // Filter By Company
            if ( !!this.props.search.company && (
                ( !user.company ) ||
                ( user.company && parseInt(user.company.id ) !== parseInt( this.props.search.company ) )
            ) ) {
                return false;
            }

            // Filter By Industry
            if (
                ( !!this.props.search.industry && !user.primaryIndustry ) ||
                ( !!this.props.search.industry && parseInt(user.primaryIndustry.id ) !== parseInt( this.props.search.industry ) )
            ) {
                return false;
            }

            // Filter By Sub Industry
            if ( !!this.props.search.secondaryIndustry && user.secondaryIndustries.filter(secondaryIndustry => parseInt( secondaryIndustry.id ) === parseInt( this.props.search.secondaryIndustry ) ).length === 0 ) {
                return false;
            }

            return true;
        })

    }

    renderCompanyDropdown() {

        if ( this.props.companies.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateCompanyQuery}>
                        <option value="">Filter by Company...</option>
                        { this.props.companies.map( company => <option key={company.id} value={company.id}>{company.name}</option> ) }
                    </select>
                    <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                            tabIndex="-1">
                        <span></span>
                        <span data-uk-icon="icon: chevron-down"></span>
                    </button>
                </div>
            </div>
        }

        return null;
    }

    renderIndustryDropdown() {

        if ( this.props.industries.length > 0 ) {
            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updatePrimaryIndustryQuery}>
                        <option value="">Filter by Industry...</option>
                        { this.props.industries.map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
                    </select>
                    <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                            tabIndex="-1">
                        <span></span>
                        <span data-uk-icon="icon: chevron-down"></span>
                    </button>
                </div>
            </div>
        }

        return null;
    }

    renderSecondaryIndustryDropdown() {

        if ( this.props.industries.length > 0 ) {

            const secondaryIndustries = this.props.industries.map( industry => {
                return parseInt(this.props.search.industry ) === parseInt( industry.id ) ? industry.secondaryIndustries : [];
            } ).reduce((a, b) => a.concat(b), []).filter((v,i,a)=>a.findIndex((t)=>(t.id === v.id))===i);

            return <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateSecondaryIndustryQuery}>
                        <option value="">Filter by Career...</option>
                        { secondaryIndustries.map( industry => <option key={industry.id} value={industry.id}>{industry.name}</option> ) }
                    </select>
                    <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                            tabIndex="-1">
                        <span></span>
                        <span data-uk-icon="icon: chevron-down"></span>
                    </button>
                </div>
            </div>
        }

        return null;
    }

    componentDidMount() {
        this.props.onNotifyButtonClick( this.props.form.url );
    }
}

App.propTypes = {};
App.defaultProps = {};

export const mapStateToProps = (state = {}) => ({
    companies: state.companies,
    industries: state.industries,
    users: state.users,
    roles: state.roles,
    search: state.search,
    ui: state.ui,
    form: state.form,
    experienceId: state.experienceId
});

export const mapDispatchToProps = dispatch => ({
    updateCompanyQuery: (event) => dispatch(updateCompanyQuery(event.target.value)),
    updatePrimaryIndustryQuery: (event) => dispatch(updatePrimaryIndustryQuery(event.target.value)),
    updateSearchQuery: (event) => dispatch(updateSearchQuery(event.target.value)),
    updateSecondaryIndustryQuery: (event) => dispatch(updateSecondaryIndustryQuery(event.target.value)),
    onNotifyButtonClick: (url) => dispatch(onNotifyButtonClick(url)),
    onFormSubmit: (event, url) => dispatch(onFormSubmit(event, url)),
    onSelectFieldChange: (event) => dispatch(onSelectFieldChange(event)),
    onTextareaFieldChange: (event) => dispatch(onTextareaFieldChange(event)),
    closeModal: (event) => dispatch(closeModal(event))
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
