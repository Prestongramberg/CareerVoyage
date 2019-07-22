import React from "react"
import PropTypes from "prop-types";

class App extends React.Component {

    constructor() {
        super();
        const methods = ["addResource"];
        methods.forEach(method => (this[method] = this[method].bind(this)));

        this.state = { resources: [] };
    }

    render() {

        return (

            <div>
                {this.state.items.map(({resource, index}) => {
                    return (
                        <div id={`edit_company_form_companyResources_${index}`} novalidate="novalidate">
                            <div>
                                <input type="file" id={`edit_company_form_companyResources_${index}_file`}
                                       name={`edit_company_form[companyResources][${index}][file]`}
                                       required="required"/>
                            </div>
                            <div>
                                <label htmlFor={`edit_company_form_companyResources_${index}_title`}
                                       className="required">Title</label>
                                <input type="text"
                                       id={`edit_company_form_companyResources_${index}_title`}
                                       name={`edit_company_form[companyResources][${index}][title]`}
                                       required="required"/>
                            </div>
                            <div>
                                <label htmlFor={`edit_company_form_companyResources_${index}_description`}
                                       className="required">Description</label>
                                <textarea
                                    id={`edit_company_form_companyResources_${index}_description`}
                                    name={`edit_company_form[companyResources][${index}][description]`}
                                    required="required"></textarea></div>
                        </div>
                    )
                })}

                <a onClick={this.addResource} className="js-addResource"><i className="fa fa-plus" aria-hidden="true"></i> Add a Resource</a>
            </div>
        )
    }

    addResource() {
        let resources = [ ...this.state.resources ]
        resources.push([])
        this.setState({
            resources
        })
    }

    componentDidMount() {
        this.setState({
            resources: this.props.resources
        })
        console.log(this.props.resources);
    }
}

App.propTypes = {
    resources: PropTypes.array
};

App.defaultProps = {
    resources: []
};

export default App;