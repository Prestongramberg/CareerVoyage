import * as actionTypes from "./actionTypes";
import * as api  from '../../../utilities/api/api'

export function showHistory() {
    return {
        type: actionTypes.SHOW_HISTORY
    }
}

export function showSearch() {
    return {
        type: actionTypes.SHOW_SEARCH
    }
}

export function openChat() {
    return {
        type: actionTypes.OPEN_CHAT
    }
}

export function closeChat() {
    return {
        type: actionTypes.CLOSE_CHAT
    }
}

export function openThread() {
    return {
        type: actionTypes.OPEN_THREAD
    }
}

export function closeThread() {
    return {
        type: actionTypes.CLOSE_THREAD
    }
}

export function updateMessage( message ) {
    return {
        type: actionTypes.UPDATE_MESSAGE,
        message: message
    }
}

export function updateSearch( search ) {
    return {
        type: actionTypes.UPDATE_SEARCH,
        search: search
    }
}

export function handlePusherEvent( data ) {
    return (dispatch, getState) => {

        const { chat, ui } = getState();

        if( typeof data !== "object" || !data.hasOwnProperty("chat") ) {
            return;
        }

        if ( ui.isThreadOpen ) {
            chat.chatId === data.chat.id ? dispatch(loadThread( chat.userEngagedWith.id, true )) : dispatch({ type: actionTypes.INCREMENT_LIVE_CHAT })
        } else if ( ui.isChatOpen ) {
            dispatch(loadChatHistory())
        } else {
            dispatch({ type: actionTypes.INCREMENT_LIVE_CHAT })
        }
    }
}

export function loadChatHistory() {
    return (dispatch, getState) => {

        const url = window.Routing.generate("get_chat_history", { id: window.SETTINGS.LOGGED_IN_USER_ID })

        dispatch({type: actionTypes.LOADING_CHAT_HISTORY})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    const data = response.responseBody.data;
                    const unreadMessages = data.reduce(( accumulator, currentValue ) => ( accumulator + currentValue.unread_messages ), 0 )
                    dispatch({type: actionTypes.LOADING_CHAT_HISTORY_SUCCESS, usersHistory: response.responseBody.data, unreadMessages: unreadMessages })
                }  else {
                    dispatch({
                        type: actionTypes.LOADING_CHAT_HISTORY_FAILURE
                    })
                    window.Pintex.notification("Something went wrong, please try again.");
                }
            })
            .catch(()=> {
                dispatch({
                    type: actionTypes.LOADING_THREAD_FAILURE
                })
                window.Pintex.notification("Something went wrong, please try again.");
            })
    }
}

export function loadThread( userId, refresh = false ) {

    return (dispatch, getState) => {

        const url = window.Routing.generate("create_or_get_chat")

        !refresh && dispatch({type: actionTypes.LOADING_THREAD})

        return api.post(url, { userId: userId })
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    const data = response.responseBody.data;
                    const engagedUser = parseInt(data.userOne.id ) === parseInt( userId ) ? data.userOne : data.userTwo;
                    dispatch({type: actionTypes.LOADING_THREAD_SUCCESS, messages: data.messages, chatId: data.id, userEngagedWith: engagedUser })
                }  else {
                    dispatch({
                        type: actionTypes.LOADING_THREAD_FAILURE
                    })
                    window.Pintex.notification("Something went wrong, please try again.");
                }
            })
            .catch(()=> {
                dispatch({
                    type: actionTypes.LOADING_THREAD_FAILURE
                })
                window.Pintex.notification("Something went wrong, please try again.");
            })
    }
}

export function sendMessage( message, chatId ) {
    return (dispatch, getState) => {

        const url = window.Routing.generate("message_chat", { chatId: chatId })

        dispatch({type: actionTypes.SEND_MESSAGE})

        return api.post(url, { message: message })
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.SEND_MESSAGE_SUCCESS })

                    // Refresh the chat
                    const { chat } = getState();
                    dispatch(loadThread( chat.userEngagedWith.id, true ))
                }  else {
                    dispatch({
                        type: actionTypes.SEND_MESSAGE_FAILURE
                    })
                    window.Pintex.notification("Something went wrong, please try again.");
                }
            })
            .catch(()=> {
                dispatch({
                    type: actionTypes.LOADING_THREAD_FAILURE
                })
                window.Pintex.notification("Something went wrong, please try again.");
            })
    }
}

export function search( search ) {
    return (dispatch, getState) => {

        const url = window.Routing.generate("search_chat_users", { search: search } )

        dispatch({type: actionTypes.SEARCH})

        return api.get(url)
            .then((response) => {
                if (response.statusCode < 300 && response.responseBody.success === true) {
                    dispatch({type: actionTypes.SEARCH_SUCCESS, users: response.responseBody.data })
                }  else {
                    dispatch({
                        type: actionTypes.SEARCH_FAILURE
                    })
                    window.Pintex.notification("Something went wrong, please try again.");
                }
            })
            .catch((e)=> {
                dispatch({
                    type: actionTypes.SEARCH_FAILURE
                })
                window.Pintex.notification("Something went wrong, please try again.");
            })
    }
}

export function initiateChatWithUserId( userId, message ) {
    return (dispatch, getState) => {
        dispatch({type: actionTypes.OPEN_CHAT})
        dispatch({type: actionTypes.OPEN_THREAD})
        dispatch(loadThread(userId))
        message && dispatch({type: actionTypes.POPULATE_MESSAGE, message: message})
    }
}
