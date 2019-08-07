import React from "react";
import ReactDOM from "react-dom";
import FavoriteCompany from "../../components/FavoriteCompany/FavoriteCompany";

const company_favorites = document.getElementsByClassName("react-favorite-company");
for( let i = 0; i < company_favorites.length; i++) {

    const companyId = parseInt(company_favorites[i].getAttribute("data-company-id"));
    const companyIsFavorited = !!company_favorites[i].getAttribute("data-company-favorited");

    ReactDOM.render(
        <FavoriteCompany
            id={companyId}
            isFavorited={companyIsFavorited} />,
        company_favorites[i]
    );
}