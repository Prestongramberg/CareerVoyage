import React from "react"
import { connect } from "react-redux"
import { onNotifyButtonClick, onFormSubmit, closeModal, onSelectFieldChange, onTextareaFieldChange, updateCompanyQuery, updatePrimaryIndustryQuery, updateSecondaryIndustryQuery } from './actions/actionCreators'
import Modal from 'react-modal';
import Loader from "../../components/Loader/Loader";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["notifyButton"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        return (
            <div>
                {this.notifyButton()}
                <NotificationModal {...this.props} />
            </div>
        )
    }

    notifyButton() {
        return (
            <a className="uk-button uk-button-danger uk-button-small alert alert-primary" role="alert" onClick={(event) => {this.props.onNotifyButtonClick(event, this.props.form.url) }}>
                {this.props.ui.title}
            </a>
        )
    }

    componentDidMount() {}
}

class NotificationModal extends React.Component {
    constructor() {
        super();

        const methods = [
            "renderCompanyDropdown", "renderIndustryDropdown", "renderSecondaryIndustryDropdown", "getRelevantUsers"
        ];

        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    render() {
        const isModalOpen = this.props.ui.showModal;

        return (
            <Modal
                isOpen={isModalOpen}
                onAfterOpen={() => {}}
                //onRequestClose={this.props.closeModal}
                className="react-modal__modal"
                overlayClassName="react-modal"
            >

                <h2>{this.props.ui.title}</h2>
                <br />

                { this.renderCompanyDropdown() }
                { this.renderIndustryDropdown() }
                { this.renderSecondaryIndustryDropdown() }

                <button type="button" className="close" aria-label="Close" onClick={this.props.closeModal}>
                    <span aria-hidden="true">&times;</span>
                </button>

                <form className="form-inline" onSubmit={(event) => { this.props.onFormSubmit(event, window.Routing.generate('experience_notify_users', {id: this.props.experienceId})) }}>

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
                                <p>No users were found.  Please try again later.</p>
                            )
                        }
                    </div>

                    <div className="customMessage">
                        <label>Custom Notification Message</label>
                        <textarea className="uk-textarea" name="customMessage" onChange={this.props.onTextareaFieldChange}></textarea>
                    </div>

                    <button type="submit" className="btn btn-primary">Send Notification</button>

                </form>

            </Modal>
        );
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
    onNotifyButtonClick: (event, url) => dispatch(onNotifyButtonClick(event, url)),
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
