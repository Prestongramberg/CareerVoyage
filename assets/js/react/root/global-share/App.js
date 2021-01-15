import React, { Component } from 'react';
import PropTypes from "prop-types";
import { addUser, removeUser, searchChattableUsers, sendNotifications, updateMessage, queryByRole, queryByUserRole, queryByCompany, queryByInterests, queryByCompanyAdministrators, queryBySchool, queryByCourseTaught, queryByPrimaryIndustry, queryBySecondaryIndustry, query, queryByPage } from "./actions/actionCreators";
import {connect} from "react-redux";
const cb = 'global-share';
import {mapObject} from "../../utilities/object-utils";
import MultiSelect from "react-multi-select-component";

export class App extends Component {

    constructor(props) {
        super(props);
        const methods = ["queryByUserRole", "queryByRole", "queryByCompany", "queryByCompanyAdministrators", "queryByCourseTaught", "queryBySchool", "queryByPrimaryIndustry", "queryBySecondaryIndustry", "queryByInterests"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    componentWillMount() {
        this.props.updateMessage( this.props.message )
        this.props.addDefaultUsers()
    }

    render() {

        debugger;

        const users = this.getRelevantUsers();
        const userCount = this.props.filters.total_count;

        return (
            <div className={cb}>
                <button data-uk-toggle="target: #global-share" type="button"
                        className="uk-button uk-button-primary uk-button-small">
                    {this.props.title}
                </button>
                <div id="global-share" className="uk-modal-800" data-uk-modal>
                    <div className="uk-modal-dialog uk-modal-body">
                        <h3>Who would you like to share with?</h3>

                        <form id="filterForm">
                            <div className="uk-search uk-search-default uk-width-1-1">
                                <span data-uk-search-icon></span>
                                {<input className="uk-search-input" type="search" placeholder="Search..." onChange={this.props.searchChattableUsers} value={this.props.search.query} />}
                            </div>

                            <ul uk-accordion="multiple: true">
                                <li>
                                    <a className="uk-accordion-title" href="#">Filters</a>
                                    <div className="uk-accordion-content">

                                        <div className="uk-container">
                                            <div className="uk-grid" uk-grid>
                                                <div className="uk-width-1-3">
                                                    { this.renderUserRoleDropdown() }
                                                </div>
                                                { this.isProfessionalUserRoleSelected() && <div className="uk-width-1-3"> { this.renderRoleDropdown() } </div> }
                                                { this.isProfessionalUserRoleSelected() && <div className="uk-width-1-3"> { this.renderCompanyDropdown() } </div> }
                                                { ( this.isProfessionalUserRoleSelected() || this.isEducatorUserRoleSelected() || this.isStudentUserRoleSelected() ) && <div className="uk-width-1-3"> { this.renderInterestsSearchField() } </div> }
                                                { this.isProfessionalUserRoleSelected() && <div className="uk-width-1-3"> { this.renderCompanyAdministratorDropdown() } </div> }

                                                { this.isEducatorUserRoleSelected() && <div className="uk-width-1-3"> { this.renderCoursesTaughtDropdown() } </div> }
                                                { ( !this.props.user.roles.includes('ROLE_EDUCATOR_USER') && (this.isEducatorUserRoleSelected() || this.isStudentUserRoleSelected() || this.isSchoolAdministratorUserRoleSelected()) ) && <div className="uk-width-1-3"> { this.renderSchoolDropdown() } </div> }

                                                { ( this.isProfessionalUserRoleSelected() || this.isEducatorUserRoleSelected() || this.isStudentUserRoleSelected() ) && <div className="uk-width-1-3"> { this.renderPrimaryIndustryDropdown() } </div> }
                                                { ( this.isProfessionalUserRoleSelected() || this.isEducatorUserRoleSelected() || this.isStudentUserRoleSelected() ) && <div className="uk-width-1-3"> { this.renderSecondaryIndustryDropdown() } </div> }
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </form>

                        <div className="uk-margin">
                            <div className="uk-grid">
                                <div className="uk-width-1-2">
                                    <div className={`${cb}__heading`}>{users.length > 0 && `Sending to Users: (${this.props.ui.users.length} results)`} {users.length === 0 && `Select from a filter above to get started...`}</div>
                                    <div className={`${cb}__scrollable`}>
                                        {this.props.ui.users.map((user) => {
                                            return (
                                                <div key={user.id}
                                                     className={`${cb}__user-remove`}
                                                     onClick={() => { this.props.removeUser( user ) }}>
                                                    <div className="uk-grid">
                                                        <div className="uk-width-expand">
                                                            {this.renderUser(user)}
                                                        </div>
                                                        <div className="uk-width-auto">
                                                            <span uk-icon="icon: close"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            )
                                        })}
                                    </div>
                                </div>
                                <div className="uk-width-1-2">
                                    <div className={`${cb}__heading`}>{users.length > 0 && `Search Found: (${userCount} results)`}</div>
                                    <div className={`${cb}__scrollable`}>
                                        {users.map((user) => {
                                            return (

                                                <div key={user.id} className={`live-chat__window-chat-thread ${cb}__user-add`} onClick={() => { this.props.addUser( user ) }}>
                                                    {this.renderUser(user)}
                                                </div>

                                            )
                                        })}
                                    </div>

                                    {users.length > 0 && (
                                        <ul className="uk-pagination">
                                            <li onClick={() => { this.changePage(this.props.filters.current_page > 1 ? this.props.filters.current_page - 1 : 1) }}><a href="javascript:void(0)">prev</a></li>
                                            <li onClick={() => { this.changePage(this.props.filters.current_page === this.props.filters.total_pages ? this.props.filters.total_pages : this.props.filters.current_page + 1) }}><a href="javascript:void(0)">next</a></li>
                                        </ul>
                                    )}

                                </div>
                            </div>
                        </div>
                        <div className="uk-margin">
                            <label>Message</label>
                            {<textarea id="" className="uk-textarea" cols="30" rows="10" onChange={ (e) => { this.props.updateMessage( e.target.value ) } } value={ this.props.ui.message }></textarea>}
                        </div>
                        <div className="uk-margin">
                            {<button className="uk-button uk-button-primary" style={{'width': '100%'}} onClick={() => { this.props.sendNotifications() }}>Share to selected users</button>}
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    renderUser(user) {

        let loggedInUser = this.props.user;
        let row = '';
        let photoImageURL = user.photoImageURL ? user.photoImageURL : '/build/images/avatar.ec6ae432.png';

        switch (user.user_role) {
            case 'professional':
                row = user.first_name + ' ' +  user.last_name + ', Professional';

                if(user.company_name) {
                    row += ', ' + user.company_name;
                }

                break;
            case 'educator':
                row = user.first_name + ' ' +  user.last_name + ', Educator, ' + user.school_name;
                break;
            case 'student':

                // if the logged in user is a professional then don't show the student name
                if(loggedInUser.roles && loggedInUser.roles.indexOf("ROLE_PROFESSIONAL_USER") !== -1 ) {
                    row = 'Student, ' + user.school_name;
                    photoImageURL = '/build/images/avatar.ec6ae432.png';
                } else {
                    row = user.first_name + ' ' +  user.last_name + ', Student, ' + user.school_name;
                }

                break;
            case 'school_administrator':
                row = user.first_name + ' ' +  user.last_name + ', School Administrator';
                break;
        }

        return (
            <React.Fragment>
                <div className="live-chat__window-chat-thread-image">
                    <img src={photoImageURL} />
                </div>
                <div className="live-chat__window-chat-thread-name">
                    {row}
                </div>
            </React.Fragment>
        )
    }

    renderRoleDropdown() {

        if ( this.props.filters.roles.length > 0) {
            return (
                <div>
                    <MultiSelect
                        overrideStrings={{"selectSomeItems": "Volunteer Role"}}
                        options={this.props.filters.roles}
                        value={this.props.search.roles}
                        onChange={this.queryByRole}
                    />
                </div>
            );
        }

        return null;
    }

    renderUserRoleDropdown() {

        if ( this.props.filters.user_roles.length > 0) {
            return (
                <div>
                    <MultiSelect
                        overrideStrings={{"selectSomeItems": "User Role"}}
                        options={this.props.filters.user_roles}
                        value={this.props.search.user_roles}
                        onChange={this.queryByUserRole}
                    />
                </div>
            );
        }

        return null;
    }

    renderCompanyDropdown() {

        if ( this.props.filters.companies.length > 0) {
            return (
                <div>
                    <MultiSelect
                        overrideStrings={{"selectSomeItems": "Company"}}
                        options={this.props.filters.companies}
                        value={this.props.search.companies}
                        onChange={this.queryByCompany}
                    />
                </div>
            );
        }

        return null;
    }

    renderInterestsSearchField() {

        return (
            <div className={"uk-search uk-search-default"} style={{"width" : "100%"}}>
                <span data-uk-search-icon></span>
                <input className="uk-search-input" type="search" placeholder="Search Interests..." onChange={this.queryByInterests} value={this.props.search.interests} />
            </div>
        );
    }

    renderCompanyAdministratorDropdown() {

        if ( this.props.filters.company_admins.length > 0) {
            return (
                <div>
                    <MultiSelect
                        overrideStrings={{"selectSomeItems": "Company Administrator"}}
                        options={this.props.filters.company_admins}
                        value={this.props.search.company_admins}
                        onChange={this.queryByCompanyAdministrators}
                    />
                </div>
            );
        }

        return null;
    }

    renderCoursesTaughtDropdown() {

        if ( this.props.filters.courses_taught.length > 0) {
            return (
                <div>
                    <MultiSelect
                        overrideStrings={{"selectSomeItems": "Course Taught"}}
                        options={this.props.filters.courses_taught}
                        value={this.props.search.courses_taught}
                        onChange={this.queryByCourseTaught}
                    />
                </div>
            );
        }

        return null;
    }

    renderSchoolDropdown() {

        return (
            <div>
                <MultiSelect
                    overrideStrings={{"selectSomeItems": "School"}}
                    options={this.props.filters.schools}
                    value={this.props.search.schools}
                    onChange={this.queryBySchool}
                />
            </div>
        );
    }

    renderPrimaryIndustryDropdown() {

        if ( this.props.filters.primary_industries.length > 0) {
            return (
                <div>
                    <MultiSelect
                        overrideStrings={{"selectSomeItems": "Primary Industry"}}
                        options={this.props.filters.primary_industries}
                        value={this.props.search.primary_industries}
                        onChange={this.queryByPrimaryIndustry}
                    />
                </div>
            );
        }

        return null;

    }

    renderSecondaryIndustryDropdown() {

        if ( this.props.search.primary_industries.length > 0) {

            let filters = [];
            let selectedPrimaryIndustries = this.props.search.primary_industries.map((x)=>x.value);
            let addedSecondaryIndustries = [];

            this.props.filters.secondary_industries.forEach(function(industryData) {

                let primaryIndustryId = industryData.primaryIndustryId;

                if(selectedPrimaryIndustries.includes(primaryIndustryId) && !addedSecondaryIndustries.includes(industryData.secondaryIndustryId)) {

                    addedSecondaryIndustries.push(industryData.secondaryIndustryId);

                    filters.push({label: industryData.secondaryIndustryName, value: industryData.secondaryIndustryId});
                }
            });

            return (
                <div>
                    <MultiSelect
                        overrideStrings={{"selectSomeItems": "Secondary Career"}}
                        options={filters}
                        value={this.props.search.secondary_industries}
                        onChange={this.queryBySecondaryIndustry}
                    />
                </div>
            );
        }

        return null;

    }

    getRelevantUsers() {

        return this.props.users.all;

        return this.props.users.all.filter(user => {

            // roles search
            if(this.props.search.roles.length > 0) {

                if(!user.roles) {
                    return false;
                }

                let roles = Object.keys(user.roles);

                let selectedRoles = this.props.search.roles.map((x)=>x.value);
                let matchingRoles = selectedRoles.filter(value => roles.includes(value))

                if(matchingRoles.length === 0) {
                    return false;
                }
            }

            // user roles search
            if(this.props.search.user_roles.length > 0) {

                if(!user.user_role) {
                    return false;
                }

                let userRole = user.user_role;
                let selectedUserRoles = this.props.search.user_roles.map((x)=>x.value);
                let hasMatchingUserRoles = selectedUserRoles.includes(userRole);

                if(user.company_administrator && selectedUserRoles.includes('company_administrator')) {
                    hasMatchingUserRoles = true;
                }

                if(!hasMatchingUserRoles) {
                    return false;
                }
            }

            // companies search
            if(this.props.search.companies.length > 0) {

                if(!user.company_id) {
                    return false;
                }

                let companyId = user.company_id;
                let selectedCompanyIds = this.props.search.companies.map((x)=>x.value);
                let hasMatchingCompanies = selectedCompanyIds.includes(companyId);

                if(!hasMatchingCompanies) {
                    return false;
                }
            }

            // interests search
            if(this.props.search.interests !== "") {

                if(!user.interests) {
                    return false;
                }

                let interests = user.interests;

                let hasMatchingInterests = interests.toLowerCase().includes(this.props.search.interests.toLowerCase());

                if(!hasMatchingInterests) {
                    return false;
                }
            }

            // company admin search
            if(this.props.search.company_admins.length > 0) {

                if(!user.company_administrator) {
                    return false;
                }

                let companyAdministrator = user.company_administrator;
                let selectedCompanyAdmins = this.props.search.company_admins.map((x)=>x.value);
                let hasMatchingCompanyAdmin = selectedCompanyAdmins.includes(companyAdministrator);

                if(!hasMatchingCompanyAdmin) {
                    return false;
                }
            }

            // courses taught search
            if(this.props.search.courses_taught.length > 0) {

                if(!user.courses) {
                    return false;
                }

                let coursesTaught = Object.keys(user.courses);

                let selectedCoursesTaught = this.props.search.courses_taught.map((x)=>x.value);
                let hasMatchingCoursesTaught = selectedCoursesTaught.filter(value => coursesTaught.includes(value))

                if(hasMatchingCoursesTaught.length === 0) {
                    return false;
                }
            }

            // schools search
            if(this.props.search.schools.length > 0) {

                let schoolIds = [];

                if(user.schools) {
                    for (const property in user.schools) {
                        schoolIds.push(property);
                    }
                }

                if(user.school_id) {
                    schoolIds.push(user.school_id);
                }

                let selectedSchools = this.props.search.schools.map((x)=>x.value);
                let hasMatchingSchools = selectedSchools.filter(value => schoolIds.includes(value));

                if(hasMatchingSchools.length === 0) {
                    return false;
                }
            }

            if(this.props.search.primary_industries.length > 0) {

                let primaryIndustryIds = [];
                for(let property in user.secondary_industries) {
                    let industryData = user.secondary_industries[property];
                    primaryIndustryIds.push(industryData.primary_industry_id);
                }

                let selectedPrimaryIndustries = this.props.search.primary_industries.map((x)=>x.value);
                let hasMatchingPrimaryIndustries = selectedPrimaryIndustries.filter(value => primaryIndustryIds.includes(value));

                if(hasMatchingPrimaryIndustries.length === 0) {
                    return false;
                }
            }

            if(this.props.search.secondary_industries.length > 0) {

                let secondaryIndustryIds = [];
                for(let property in user.secondary_industries) {
                    let industryData = user.secondary_industries[property];
                    secondaryIndustryIds.push(industryData.secondary_industry_id);
                }

                let selectedSecondaryIndustries = this.props.search.secondary_industries.map((x)=>x.value);
                let hasMatchingSecondaryIndustries = selectedSecondaryIndustries.filter(value => secondaryIndustryIds.includes(value));

                if(hasMatchingSecondaryIndustries.length === 0) {
                    return false;
                }
            }

            return true;
        });
    }

    isProfessionalUserRoleSelected() {

        let selectedUserRoles = this.props.search.user_roles.map((x)=>x.value);
        return selectedUserRoles.includes("professional");
    }

    isEducatorUserRoleSelected() {

        let selectedUserRoles = this.props.search.user_roles.map((x)=>x.value);
        return selectedUserRoles.includes("educator");
    }

    isStudentUserRoleSelected() {

        let selectedUserRoles = this.props.search.user_roles.map((x)=>x.value);
        return selectedUserRoles.includes("student");
    }

    isSchoolAdministratorUserRoleSelected() {

        let selectedUserRoles = this.props.search.user_roles.map((x)=>x.value);
        return selectedUserRoles.includes("school_administrator");
    }

    queryByRole(options) {

        debugger;
        this.props.queryByRole(options);
        this.props.query(this.props.search);
    }


    queryByUserRole(options) {

        debugger;
        this.props.queryByUserRole(options);
        this.props.query(this.props.search);
    }

    queryByCompany(options) {

        debugger;
        this.props.queryByCompany(options);
        this.props.query(this.props.search);
    }

    queryByInterests(event) {

        debugger;
        this.props.queryByInterests(event);
        this.props.query(this.props.search);
    }

    queryByCompanyAdministrators(options) {

        debugger;
        this.props.queryByCompanyAdministrators(options);
        this.props.query(this.props.search);
    }

    queryByCourseTaught(options) {

        debugger;
        this.props.queryByCourseTaught(options);
        this.props.query(this.props.search);
    }

    queryBySchool(options) {

        debugger;
        this.props.queryBySchool(options);
        this.props.query(this.props.search);
    }

    queryByPrimaryIndustry(options) {
        debugger;
        this.props.queryByPrimaryIndustry(options);
        this.props.query(this.props.search);
    }

    queryBySecondaryIndustry(options) {
        debugger;
        this.props.queryBySecondaryIndustry(options);
        this.props.query(this.props.search);
    }

    changePage(page) {
        this.props.queryByPage(page);
        this.props.query(this.props.search);
        debugger;
    }
}

App.propTypes = {
    message: PropTypes.string,
    user: PropTypes.object
};

App.defaultProps = {
    user: {}
};

export const mapStateToProps = (state = {}) => ({
    users: state.users,
    filters: state.filters,
    search: state.search,
    ui: state.ui
});

export const mapDispatchToProps = dispatch => ({
    addUser: (user) => dispatch(addUser( user )),
    removeUser: (user) => dispatch(removeUser( user )),
    searchChattableUsers: (event) => dispatch(searchChattableUsers( event.target.value )),
    sendNotifications: () => dispatch(sendNotifications()),
    updateMessage: (message) => dispatch(updateMessage(message)),
    addDefaultUsers: () => dispatch(searchChattableUsers('')),
    queryByRole: (options) => dispatch(queryByRole( options )),
    queryByUserRole: (options) => dispatch(queryByUserRole( options )),
    query: (data) => dispatch(query( data )),
    queryByPage: (data) => dispatch(queryByPage( data )),
    queryByCompany: (options) => dispatch(queryByCompany( options )),
    queryByInterests: (event) => dispatch(queryByInterests( event.target.value )),
    queryByCompanyAdministrators: (options) => dispatch(queryByCompanyAdministrators( options )),
    queryByCourseTaught: (options) => dispatch(queryByCourseTaught( options )),
    queryBySchool: (options) => dispatch(queryBySchool( options )),
    queryByPrimaryIndustry: (options) => dispatch(queryByPrimaryIndustry( options )),
    queryBySecondaryIndustry: (options) => dispatch(queryBySecondaryIndustry( options )),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
