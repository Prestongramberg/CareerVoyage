export default function getInitialChatState( unreadMessages ) {
    return {
        currentMessage: "",
        chatId: 0,
        messages: [],
        searchTerm: "",
        unreadMessages: unreadMessages,
        usersHistory: [],
        userEngagedWith: {},
    }
}
