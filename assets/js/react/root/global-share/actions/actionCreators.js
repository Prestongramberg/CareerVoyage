import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function addUser( user ) {
    return {
        type: actionTypes.ADD_USER,
        user: user
    }
}

export function removeUser( user ) {
    return {
        type: actionTypes.REMOVE_USER,
        user: user
    }
}

export function searchChattableUsers( search ) {
    return (dispatch, getState) => {

        const url = window.Routing.generate("search_chat_users", { search: search } )

        dispatch({type: actionTypes.SEARCH_CHATTABLE_USERS, searchQuery: search})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.SEARCH_CHATTABLE_USERS_SUCCESS, users: response.responseBody.data })
                }  else {
                    dispatch({
                        type: actionTypes.SEARCH_CHATTABLE_USERS_FAILURE
                    })
                    window.Pintex.notification("Something went wrong, please try again.");
                }
            })
            .catch((e)=> {
                dispatch({
                    type: actionTypes.SEARCH_CHATTABLE_USERS_FAILURE
                })
                window.Pintex.notification("Something went wrong, please try again.");
            })
    }
}

export function sendNotifications( message ) {
    return (dispatch, getState) => {

        const { ui } = getState();

        const url = window.Routing.generate("share_chat")

        dispatch({type: actionTypes.NOTIFICATIONS_SENDING})

        return api.post(url, {
                message: message ,
                user_ids: ui.users.map((user) => user.id)
            })
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.NOTIFICATIONS_SENDING_SUCCESS });
                    window.Pintex.notification("Notifications Sent!");
                }  else {
                    dispatch({
                        type: actionTypes.NOTIFICATIONS_SENDING_FAILURE
                    })
                    window.Pintex.notification("Something went wrong, please try again.");
                }
            })
            .catch(()=> {
                dispatch({
                    type: actionTypes.NOTIFICATIONS_SENDING_FAILURE
                })
                window.Pintex.notification("Something went wrong, please try again.");
            })

    }
}
















//
// export function search( search ) {
//     return (dispatch, getState) => {
//
//         const url = window.Routing.generate("search_chat_users", { search: search } )
//
//         dispatch({type: actionTypes.SEARCH})
//
//         return api.get(url)
//             .then((response) => {
//                 if (response.statusCode < 300 && response.responseBody.success === true) {
//                     dispatch({type: actionTypes.SEARCH_SUCCESS, users: response.responseBody.data })
//                 }  else {
//                     dispatch({
//                         type: actionTypes.SEARCH_FAILURE
//                     })
//                     window.Pintex.notification("Something went wrong, please try again.");
//                 }
//             })
//             .catch((e)=> {
//                 dispatch({
//                     type: actionTypes.SEARCH_FAILURE
//                 })
//                 window.Pintex.notification("Something went wrong, please try again.");
//             })
//     }
// }
//
// export function sendMessage( message, chatId ) {
//     return (dispatch, getState) => {
//
//         const url = window.Routing.generate("message_chat", { chatId: chatId })
//
//         dispatch({type: actionTypes.SEND_MESSAGE})
//
//         return api.post(url, { message: message })
//             .then((response) => {
//                 if (response.statusCode < 300 && response.responseBody.success === true) {
//                     dispatch({type: actionTypes.SEND_MESSAGE_SUCCESS })
//
//                     // Refresh the chat
//                     const { chat } = getState();
//                     dispatch(loadThread( chat.userEngagedWith.id, true ))
//                 }  else {
//                     dispatch({
//                         type: actionTypes.SEND_MESSAGE_FAILURE
//                     })
//                     window.Pintex.notification("Something went wrong, please try again.");
//                 }
//             })
//             .catch(()=> {
//                 dispatch({
//                     type: actionTypes.LOADING_THREAD_FAILURE
//                 })
//                 window.Pintex.notification("Something went wrong, please try again.");
//             })
//     }
// }
//
// export function loadThread( userId, refresh = false ) {
//
//     return (dispatch, getState) => {
//
//         const url = window.Routing.generate("create_or_get_chat")
//
//         !refresh && dispatch({type: actionTypes.LOADING_THREAD})
//
//         return api.post(url, { userId: userId })
//             .then((response) => {
//                 if (response.statusCode < 300 && response.responseBody.success === true) {
//                     const data = response.responseBody.data;
//                     const engagedUser = parseInt(data.userOne.id ) === parseInt( userId ) ? data.userOne : data.userTwo;
//                     dispatch({type: actionTypes.LOADING_THREAD_SUCCESS, messages: data.messages, chatId: data.id, userEngagedWith: engagedUser })
//                 }  else {
//                     dispatch({
//                         type: actionTypes.LOADING_THREAD_FAILURE
//                     })
//                     window.Pintex.notification("Something went wrong, please try again.");
//                 }
//             })
//             .catch(()=> {
//                 dispatch({
//                     type: actionTypes.LOADING_THREAD_FAILURE
//                 })
//                 window.Pintex.notification("Something went wrong, please try again.");
//             })
//     }
// }

