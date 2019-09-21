export default function getInitialChatState() {
    return {
        endpoints: {
            "getChatHistory": window.Routing.generate("getChatHistory")
        }
    }
}
