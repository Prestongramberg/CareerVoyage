import React, { Component } from "react";
import PropTypes from "prop-types";
import { truncate } from "../../utilities/string-utils";

class Company extends Component {

    render() {
        return (
            <div className="uk-width-1-1 uk-width-1-2@s uk-width-1-3@l">
                <div className="uk-width-1-1 uk-text-truncate" data-uk-form-custom="target: > * > span:first-child">
                    <select onChange={this.props.updateCompanyQuery}>
                        <option value="">Filter by Company...</option>
                        { this.props.companies.map( company => <option key={company.id} value={company.id}>{company.name}</option> ) }
                    </select>
                    <button className="uk-button uk-button-default uk-width-1-1 uk-width-autom@l" type="button"
                            tabIndex="-1">
                        <span></span>
                        <span data-uk-icon="icon: chevron-down"></span>
                    </button>
                </div>
            </div>
        );
    }
}

Company.propTypes = {
    companies: PropTypes.array,
    updateCompanyQuery: PropTypes.func
};

Company.defaultProps = {
    companies: []
};

export default Company;
