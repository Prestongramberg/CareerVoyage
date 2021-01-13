import React, { Component } from "react";
import PropTypes from "prop-types";
import { truncate } from "../../utilities/string-utils";

class Company extends Component {

    constructor(props) {
        super(props);
        this.handleChange = this.handleChange.bind(this);
        this.state = {
            items: [
                { id: 0, label: "item 1" },
                { id: 2, label: "item 2", disabled: true },
                { id: 3, label: "item 3", disabled: false },
                { id: 4, label: "item 4" }
            ],
            selectedItems: []
        };
    }

    handleChange(selectedItems) {
        this.setState({ selectedItems });
    }
    render() {
        const { items, selectedItems } = this.state;
        return (
            <MultiSelect
                items={items}
                selectedItems={selectedItems}
                onChange={this.handleChange}
            />
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
