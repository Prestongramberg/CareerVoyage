import * as actionTypes from "./actionTypes";
import * as api from '../../../utilities/api/api'

/**
 * Three dispatches need to happen here
 * LOAD_INITIAL_DATA_REQUESTED
 * LOAD_INITIAL_DATA_SUCCESS
 * LOAD_INITIAL_DATA_FAILURE
 *
 *
 * @return {function(*, *): *}
 */
export function loadInitialData() {

    return (dispatch, getState) => {

        debugger;
        let state = getState();

        dispatch({
            type: actionTypes.LOAD_INITIAL_DATA_REQUESTED
        });

        let url = window.Routing.generate("search_users");

        if (state.search.experience) {
            url = window.Routing.generate("search_users", {experience: state.search.experience});
        }

        if (state.search.request) {
            url = window.Routing.generate("search_users", {request: state.search.request});
        }

        debugger;
        const data = {
            filters: state.form
        };

        debugger;

        return api.post(url, data)
            .then((response) => {


                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.SEARCH_SUCCESS, data: response.responseBody})
                }
            })
            .catch((e) => {

            })
    }
}

export function filterChanged(context) {


    return (dispatch, getState) => {


        let state = getState();

        if (state.search.typingTimeout) {
            clearTimeout(state.search.typingTimeout);
        }

        dispatch({
            type: actionTypes.FILTER_CHANGE_REQUESTED,
            context: context,
            typingTimeout: setTimeout(() => {

                let form = context.form;

                if(context.fieldName === 'userRole') {
                    form = removeByKey(form, 'company');
                    form = removeByKey(form, 'rolesWillingToFulfill');
                    form = removeByKey(form, 'primaryIndustry');
                    form = removeByKey(form, 'secondaryIndustries');
                    form = removeByKey(form, 'school');
                    form = removeByKey(form, 'myCourses');

                    if(!context.value) {
                        form = removeByKey(form, 'userRole');
                    }
                }

                const data = {
                    filters: {...form, [context.fieldName]: context.value}
                };

                const url = window.Routing.generate("search_users");

                return api.post(url, data)
                    .then((response) => {


                        if (response.statusCode < 300 && response.responseBody.success === true) {
                            dispatch({type: actionTypes.FILTER_CHANGE_SUCCESS, data: response.responseBody})
                        }
                    })
                    .catch((e) => {
                        dispatch({type: actionTypes.FILTER_CHANGE_FAILURE, context: context})
                    })

            }, 200)

        });
    }
}

export function pageChanged(pageNumber) {


    return (dispatch, getState) => {


        dispatch({
            type: actionTypes.PAGE_CHANGE_REQUESTED
        });

        const url = window.Routing.generate("search_users", {page: pageNumber});

        return api.post(url, {})
            .then((response) => {


                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.PAGE_CHANGE_SUCCESS, data: response.responseBody})
                }
            })
            .catch((e) => {
                dispatch({type: actionTypes.FILTER_CHANGE_FAILURE, context: context})
            })
    }
}

export function sendNotifications(userId, experienceId, requestId, message) {

    return (dispatch, getState) => {

        const url = window.Routing.generate("api_global_share_notify")

        dispatch({type: actionTypes.NOTIFICATIONS_SENDING, userId: userId})


        return api.post(url, {
            message: message,
            userId: userId,
            experienceId: experienceId,
            requestId: requestId
        })
            .then((response) => {

                debugger;
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    //window.Pintex.notification("Notifications Sent!");

                    UIkit.notification("Notification Sent!", {status: 'success', pos: 'top-right'})

                    dispatch({type: actionTypes.NOTIFICATIONS_SENDING_SUCCESS, userId: userId});
                } else {

                    dispatch({
                        type: actionTypes.NOTIFICATIONS_SENDING_FAILURE
                    })
                    window.Pintex.notification("Something went wrong, please try again.");
                }
            })
            .catch((response) => {

                dispatch({
                    type: actionTypes.NOTIFICATIONS_SENDING_FAILURE
                })
                window.Pintex.notification("Something went wrong, please try again.");
            })

    }
}

export function updateMessage( message, userId ) {
    debugger;
    return {
        type: actionTypes.UPDATE_MESSAGE,
        message: message,
        userId: userId
    }
}

function removeByKey(myObj, deleteKey) {
    return Object.keys(myObj)
        .filter(key => key !== deleteKey)
        .reduce((result, current) => {
            result[current] = myObj[current];
            return result;
        }, {});
}