export default function getInitialUiState( unreadMessages ) {
    return {
        isChatLoading: false,
        isChatOpen: false,
        isSearchActive: false,
        isThreadLoading: false,
        isThreadOpen: false,
        unreadMessages: unreadMessages,
    }
}
