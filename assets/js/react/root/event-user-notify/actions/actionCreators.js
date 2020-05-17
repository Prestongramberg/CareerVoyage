import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function updateCompanyQuery(company) {
    return {
        type: actionTypes.COMPANY_QUERY_CHANGED,
        company: company
    };
}

export function updatePrimaryIndustryQuery(industry) {
    return {
        type: actionTypes.PRIMARY_INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function updateSecondaryIndustryQuery(industry) {
    return {
        type: actionTypes.SECONDARY_INDUSTRY_QUERY_CHANGED,
        industry: industry
    };
}

export function onNotifyButtonClick(event, url) {

    debugger;

    return (dispatch, getState) => {

        dispatch({type: actionTypes.USERS_LOADING })
        dispatch({type: actionTypes.NOTIFY_BUTTON_CLICKED})
        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300) {
                    dispatch({type: actionTypes.USERS_LOADING_SUCCESS, response: response.responseBody})
                }  else {
                     dispatch({
                         type: actionTypes.USERS_LOADING_FAILURE
                     })
                }
            })
            .catch(()=> dispatch({
                type: actionTypes.USERS_LOADING_FAILURE
            }));
    }
}

export function onFormSubmit(event, url) {
    debugger;
    event.preventDefault();
    return (dispatch, getState) => {
        const { form }  = getState();
        debugger;
        return api.post(url, form)
            .then((response) => {
                if (response.statusCode < 300) {
                    window.Pintex.notification("Notifications successfully sent.");
                    dispatch({
                        type: actionTypes.NOTIFICATIONS_SENT
                    })
                }  else {
                    dispatch({
                        type: actionTypes.USERS_LOADING_FAILURE
                    })
                }
            })
            .catch(()=> dispatch({
                type: actionTypes.USERS_LOADING_FAILURE
            }));
    }
}

export function onSelectFieldChange(event) {

    let options = event.target.options;
    let value = [];
    for (let i = 0, l = options.length; i < l; i++) {
        if (options[i].selected) {
            value.push(options[i].value);
        }
    }

    return {
        type: actionTypes.SELECT_FIELD_CHANGED,
        users: value
    };
}

export function onTextareaFieldChange(event) {

    return {
        type: actionTypes.TEXTAREA_FIELD_CHANGED,
        customMessage: event.currentTarget.value
    };
}

export function closeModal(event) {

    return {
        type: actionTypes.CLOSE_BUTTON_CLICKED
    };
}

