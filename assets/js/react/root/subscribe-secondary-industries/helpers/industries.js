export function getSecondaryIndustry(industries, secondaryIndustryId) {

    for( var i = 0; i < industries.length; i++ ) {
        const foundSecondaryIndustry = industries[i].secondaryIndustries.find(secondaryIndustry => secondaryIndustry.id === secondaryIndustryId);
        if ( foundSecondaryIndustry ) {
            return foundSecondaryIndustry;
        }
    }

    return null;
}

export function getAllSecondaryIndustries( industries, secondaryIndustryIds ) {

    let foundSecondaryIndustries = [];

    industries.forEach(primaryIndustry => {
        const subscribedSecondaryIndustries = primaryIndustry.secondaryIndustries.filter(secondaryIndustry => secondaryIndustryIds.indexOf(secondaryIndustry.id) > -1 );
        foundSecondaryIndustries = foundSecondaryIndustries.concat( subscribedSecondaryIndustries );
    });

    return foundSecondaryIndustries;
}