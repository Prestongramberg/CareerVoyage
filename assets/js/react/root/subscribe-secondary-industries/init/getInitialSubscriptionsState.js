export default function getInitialSubscriptionsState( subscriptionData ) {
    if( Array.isArray(subscriptionData) && typeof subscriptionData[0] === "object" && subscriptionData[0].id ) {
        return subscriptionData.map(subscription => subscription.id);
    }

    return [];
}