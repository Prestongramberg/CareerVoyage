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


// Used to search the list of secondary industries
// export function searchBySecondaryIndustry(industries, query) {
//     let foundSecondaryIndustries = [];
    
//     console.log(industries);
//     console.log(query);
    
//     // Search the list of json items and find the id
//     // pass into reducer

//     return foundSecondaryIndustries;
// }

export function getSearchedSecondaryIndustries(industries, query) {
    if(query.length > 2) {
        const foundIndustries = industries.filter( industry => industry.name.toLowerCase().includes(query.toLowerCase()) );
        if(foundIndustries) {
            return foundIndustries;
        }
        // let foundIndustries = []
        // industries.forEach(industry => {
        //     if(industry.name.toLowerCase().includes(query.toLowerCase())) {
        //         foundIndustries.push(industry);
        //     }
        // });
        // return foundIndustries;
    } else {
        return [];
    }

}