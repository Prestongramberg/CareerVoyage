import React, {Component} from 'react';
import PropTypes from "prop-types";
import {
    sendNotifications,
    loadInitialData,
    filterChanged,
    pageChanged,
    updateMessage
} from "./actions/actionCreators";
import {connect} from "react-redux";

const cb = 'global-share';
import {Multiselect} from 'multiselect-react-dropdown';
import Loader from "../../components/Loader/Loader"
import Pagination from "react-js-pagination";

const avatarLogoPath = require('../../../../images/avatar.png');

export class App extends Component {

    constructor(props) {
        super(props);
        const methods = ["renderFilters", "singleSelectFilterChanged", "multipleSelectFilterChanged", "textFilterChanged", "handlePageChange", "renderShareForm"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
    }

    componentDidMount() {

        this.props.loadInitialData();
    }

    /**
     * Don't do ajax fetching inside render() as this gets
     * called all the time every time the state changes
     * @return {*}
     */
    render() {

        debugger;

        let userRoleSelected = ('userRole' in this.props.filters && this.props.filters.userRole.length > 0);

        return (
            <div>
                {this.renderFilters(0, 1)}
                <div>
                    <button style={{width: "100%"}} className="uk-button uk-button-default" type="button"
                            uk-toggle="target: #toggle-usage; animation: uk-animation-slide-top">Filters
                    </button>
                    <div id="toggle-usage" hidden>
                        {this.renderFilters(1)}
                    </div>
                </div>

                {this.props.search.loading && <div className="uk-width-1-1 uk-align-center"><Loader/></div>}

               {/* {!userRoleSelected && (
                    <p>Please select a user role from the filters above to start your search</p>
                )}
*/}
                {!this.props.search.loading &&
                <div style={{marginTop: "20px"}}>

                    <div style={{marginBottom: "20px"}}>Total Results: {this.props.search.pagination.totalCount}</div>
                    {this.props.search.items.map((item) => {
                        return [
                            <div style={{display: "flex", marginBottom: "10px"}} key={item.id}>
                                {this.renderUser(item)}
                                <div>

                                    {this.props.search.notifiedUsers.includes(item.id) ?
                                        <button style={{width: "100%", opacity: ".1"}}
                                                className="uk-button uk-button-default" type="button"
                                                disabled={true}>Share</button> :
                                        <button onClick={() => {
                                            document.getElementById("toggle-usage-textarea-" + item.id).focus();
                                        }} style={{width: "100%"}} className="uk-button uk-button-default" type="button"
                                                uk-toggle={"target: #toggle-usage-" + item.id + "; animation: uk-animation-slide-top"}>Share
                                        </button>}

                                </div>
                            </div>,
                            <div>
                                {this.renderShareForm(item)}
                            </div>
                        ]
                    })}

                    {!this.props.search.loading && this.props.search.items.length === 0 && (
                        <p>No users match your selection</p>
                    )}

                    {this.renderPagination()}
                </div>
                }
            </div>
        );
    }

    multipleSelectFilterChanged(selectedOptions, selectedOption, fieldName) {


        let values = [];
        for (let selectedOption of selectedOptions) {
            values.push(selectedOption.id);
        }

        if (!values.length) {
            values = null;
        }

        let context = {
            'fieldName': fieldName,
            'filter': selectedOptions,
            'value': values,
            'reset': [],
            //'currentFilters': this.props.filters,
            'form': this.props.form
        };

        this.props.filterChanged(context);
    }

    singleSelectFilterChanged(selectedOptions, selectedOption, fieldName) {


        let value = null;

        if (selectedOptions.length) {
            value = selectedOption.value;
        }

        let context = {
            'fieldName': fieldName,
            'filter': selectedOptions,
            'value': value,
            'reset': null,
            //'currentFilters': this.props.filters,
            'form': this.props.form
        };

        this.props.filterChanged(context);
    }

    textFilterChanged(value, fieldName) {


        let context = {
            'fieldName': fieldName,
            'filter': value,
            'value': value,
            'form': this.props.form
        };

        this.props.filterChanged(context);
    }

    handlePageChange(pageNumber) {

        this.props.pageChanged(pageNumber);


    }

    renderFilters(startingIndex = null, endingIndex = null) {

        const filters = [];

        for (let fieldName in this.props.search.schema.properties) {

            if(this.props.search.hiddenFilters.includes(fieldName)) {
                continue;
            }

            let field = this.props.search.schema.properties[fieldName];

            if (field.items && field.items.enum && field.items.enum_titles) {
                // Select Field (Multiple)

                debugger;
                let selected = this.props.filters[fieldName] || [];

                const merged = field.items.enum.reduce((obj, key, index) => ({
                    ...obj,
                    [key]: field.items.enum_titles[index]
                }), {});
                let options = [];

                for (let key in merged) {
                    let value = merged[key];

                    options.push({label: value.charAt(0).toUpperCase() + value.slice(1).toLowerCase(), value: key, id: key});
                }

                debugger;
                options = options.sort((a, b) => (a.label > b.label) ? 1 : -1);

                filters.push(<Multiselect
                        options={options}
                        showCheckbox={true}
                        showArrow={true}
                        selectedValues={selected}
                        placeholder={field.title}
                        avoidHighlightFirstOption={true}
                        onSelect={(selectedOptions, selectedOption) => {
                            this.multipleSelectFilterChanged(selectedOptions, selectedOption, fieldName)
                        }}
                        onRemove={(selectedOptions, selectedOption) => {
                            this.multipleSelectFilterChanged(selectedOptions, selectedOption, fieldName)
                        }}
                        displayValue="label"
                    />
                );

            } else if (!field.items && field.enum && field.enum_titles) {
                // Select Field Single

                let selected = this.props.filters[fieldName] || [];

                const merged = field.enum.reduce((obj, key, index) => ({
                    ...obj,
                    [key]: field.enum_titles[index]
                }), {});
                let options = [];

                debugger;
                for (let key in merged) {
                    let value = merged[key];

                    options.push({label: value.charAt(0).toUpperCase() + value.slice(1).toLowerCase(), value: key, id: key});
                }

                debugger;
                options = options.sort((a, b) => (a.label > b.label) ? 1 : -1);

                filters.push(<Multiselect
                        options={options}
                        showCheckbox={true}
                        showArrow={true}
                        selectionLimit={1}
                        selectedValues={selected}
                        avoidHighlightFirstOption={true}
                        placeholder={field.title}
                        onSelect={(selectedOptions, selectedOption) => {
                            this.singleSelectFilterChanged(selectedOptions, selectedOption, fieldName)
                        }}
                        onRemove={(selectedOptions, selectedOption) => {
                            this.singleSelectFilterChanged(selectedOptions, selectedOption, fieldName)
                        }}
                        displayValue="label"
                    />
                );
            } else if (!field.items && !field.enum && !field.enum_titles) {
                // Text Field

                if (field.type === 'string') {
                    filters.push(
                        <div className={"uk-search uk-search-default"} style={{"width": "100%"}}>
                            <span data-uk-search-icon></span>
                            <input onChange={(event) => {
                                this.textFilterChanged(event.target.value, fieldName);
                            }} className="uk-search-input" type="search" placeholder={field.title}
                                   value={this.props.filters.search || ""}/>
                        </div>
                    );
                }
            }

        }

        if (!Number.isInteger(startingIndex)) {
            startingIndex = 0;
        }

        if (!Number.isInteger(endingIndex)) {
            endingIndex = filters.length;
        }

        return (
            <div>
                {filters.slice(startingIndex, endingIndex)}
            </div>
        )
    }

    renderUser(user) {

        debugger;

        let loggedInUser = this.props.user;
        let company = '';
        let role = '';
        let name = '';
        let photoImageURL = user.photoImageURL ? user.photoImageURL : avatarLogoPath;

        if (user.professional) {

            name = user.fullName;
            role = 'Professional';
            company = user.company && user.company.name ? user.company.name : "User Does Not Belong To A Company";

        } else if (user.educator) {
            name = user.fullName;
            role = 'Educator';
            company = user.school && user.school.name ? user.school.name : "User Does Not Belong To A School";
        } else if (user.student) {

            company = user.school && user.school.name ? user.school.name : "User Does Not Belong To A School";
            role = 'Student';
            photoImageURL = avatarLogoPath;

            // if the logged in user is a professional then don't show the student name
            if (loggedInUser.roles && loggedInUser.roles.indexOf("ROLE_PROFESSIONAL_USER") !== -1) {
                name = '';
            } else {
                name = user.fullName;
            }
        } else if (user.schoolAdministrator) {

            name = user.fullName;
            role = 'School Administrator';
        } else {
            name = user.fullName;
        }

        return (
            <React.Fragment>
                <div className="live-chat__window-chat-thread-image">
                    <img src={photoImageURL}/>
                </div>
                <div className="live-chat__window-chat-thread-name">
                    {name}
                    <small><em> {role}</em></small>
                    <br/>
                    <small>{company}</small>
                </div>
            </React.Fragment>
        )
    }

    renderPagination() {

        return (
            <div style={{marginTop: "50px"}}>
                <Pagination
                    activePage={this.props.search.pagination.currentPageNumber}
                    itemsCountPerPage={this.props.search.pagination.numItemsPerPage}
                    totalItemsCount={this.props.search.pagination.totalCount}
                    pageRangeDisplayed={5}
                    innerClass="uk-pagination uk-flex-center"
                    activeClass="uk-active"
                    disabledClass="uk-disabled"
                    onChange={this.handlePageChange}
                />
            </div>
        );
    }

    renderShareForm(item) {

        if (this.props.search.notifiedUsers.includes(item.id)) {
            return null
        }

        let message = this.props.message;
        message = message.replace("[name]", item.fullName);
        if (this.props.search.user_modified_messages[item.id]) {
            message = this.props.search.user_messages[item.id];
        }

        return (
            <div id={"toggle-usage-" + item.id} hidden>
                {<textarea id={"toggle-usage-textarea-" + item.id} className="uk-textarea" cols="30"
                           rows="5" onChange={(e) => {
                    this.props.updateMessage(e.target.value, item.id)
                }} value={message}></textarea>}
                {<button className="uk-button uk-button-primary"
                         style={{'width': '100%'}} onClick={() => {
                    this.props.sendNotifications(item.id, this.props.experience, this.props.request, message)
                }}>{this.props.search.currentNotifiedUser == item.id ? "Sending..." : "Share"}</button>
                }
            </div>
        );

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
    filters: state.filters,
    form: state.form,
    search: state.search
});

export const mapDispatchToProps = dispatch => ({

    loadInitialData: () => dispatch(loadInitialData()),
    filterChanged: (context) => dispatch(filterChanged(context)),
    pageChanged: (pageNumber) => dispatch(pageChanged(pageNumber)),
    sendNotifications: (userId, experienceId, requestId, message) => dispatch(sendNotifications(userId, experienceId, requestId, message)),
    updateMessage: (message, userId) => dispatch(updateMessage(message, userId)),
});

const ConnectedApp = connect(
    mapStateToProps,
    mapDispatchToProps
)(App);

export default ConnectedApp;
