import React, { Component } from 'react';

const cb = 'form-rating'

export class FormRating extends Component {

    constructor(props) {
        super(props);
        const methods = ["displayFormField", "getText"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
        this.state = {
            fieldValue: props.fieldValue
        };
    }

    render() {

        const { fieldName } = this.props

        return (
            <div className={cb}>
                { this.displayFormField(1) }
                { this.displayFormField(2) }
                { this.displayFormField(3) }
                { this.displayFormField(4) }
                { this.displayFormField(5) }
                <input type="hidden" name={fieldName} value={this.state.fieldValue} />
            </div>
        );
    }

    displayFormField(value) {

        const activeClass = value === parseInt( this.state.fieldValue ) ? 'form-rating__input--active' : ''

        return (
            <div className={`${cb}__input ${activeClass}`} onClick={() => {
                if ( !this.props.disabled ) {
                    this.setState({
                        fieldValue: value
                    })
                }
            }}>
                <div className={`${cb}__input-button`}>
                    { value }
                </div>
                <div className={`${cb}__input-text`}>
                    { this.getText( value ) }
                </div>
            </div>
        )
    }

    getText( value ) {
        switch(value) {
            case 1:
                return "Much less"
            case 2:
                return "Less"
            case 3:
                return "No change"
            case 4:
                return "More"
            case 5:
                return "Much more"
        }
    }
}

export default FormRating
