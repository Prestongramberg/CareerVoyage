export const OPEN_CHAT = "OPEN_CHAT"
export const CLOSE_CHAT = "CLOSE_CHAT"
export const TOGGLE_CHAT = "TOGGLE_CHAT"

export const OPEN_THREAD = "OPEN_THREAD"
export const CLOSE_THREAD = "CLOSE_THREAD"

export const SHOW_HISTORY = "SHOW_HISTORY"
export const SHOW_SEARCH = "SHOW_SEARCH"

export const LOADING_CHAT_HISTORY = "LOADING_CHAT_HISTORY"
export const LOADING_CHAT_HISTORY_SUCCESS = "LOADING_CHAT_HISTORY_SUCCESS"
export const LOADING_CHAT_HISTORY_FAILURE = "LOADING_CHAT_HISTORY_FAILURE"

export const LOADING_THREAD = "LOADING_THREAD"
export const LOADING_THREAD_SUCCESS = "LOADING_THREAD_SUCCESS"
export const LOADING_THREAD_FAILURE = "LOADING_THREAD_FAILURE"

export const SEND_MESSAGE = "SEND_MESSAGE"
export const SEND_MESSAGE_SUCCESS = "SEND_MESSAGE_SUCCESS"
export const SEND_MESSAGE_FAILURE = "SEND_MESSAGE_FAILURE"


// export const CHAT_LOAD = "CHAT_LOAD";
// export const CHAT_LOAD_SUCCESS = "CHAT_LOAD_SUCCESS";
// export const CHAT_LOAD_FAILURE = "CHAT_LOAD_FAILURE";
// // when user clicks chat window on screen
// // call "UNDEFINED" endpoint with userId to get all users that have been previously chatted with
//
// export const CHAT_CLOSE_WINDOW = "CHAT_CLOSE_WINDOW";
// // closes out chat state completely, killing any state
//
// export const CHAT_LOAD_THREAD = "CHAT_LOAD_THREAD";
// export const CHAT_LOAD_THREAD_SUCESS = "CHAT_LOAD_THREAD_SUCESS";
// export const CHAT_LOAD_THREAD_FAILURE = "CHAT_LOAD_THREAD_FAILURE";
// // when user clicks on a chat thread
// // call "create_single_chat" endpoint with userId to create a new chat stream, or return an existing one
//
// export const CHAT_SEND_MESSAGE = "CHAT_SEND_MESSAGE";
// export const CHAT_SEND_MESSAGE_SUCCESS = "CHAT_SEND_MESSAGE_SUCCESS";
// export const CHAT_SEND_MESSAGE_FAILURE = "CHAT_SEND_MESSAGE_FAILURE";
// // when user sends a message
// // call "message_chat" endpoint (/chats/{id}/message) with guid of thread and POST message
//
// // Events
// // Subscribe to global chat-${userId} with Payload information for incoming messages?
// // This will also "tick" up the "bubble" amount unless the user is already in that chat thread
