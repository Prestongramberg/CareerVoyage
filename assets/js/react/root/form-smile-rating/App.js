import React, { Component } from 'react';

import Smile1 from "../../../../images/01_VERY_SAD.png"
import Smile1Active from "../../../../images/01_VERY_SAD_CLICKED.png"
import Smile2 from "../../../../images/02_SAD.png"
import Smile2Active from "../../../../images/02_SAD_CLICKED.png"
import Smile3 from "../../../../images/03_NEUTRAL.png"
import Smile3Active from "../../../../images/03_NEUTRAL_CLICKED.png"
import Smile4 from "../../../../images/04_HAPPY.png"
import Smile4Active from "../../../../images/04_HAPPY_CLICKED.png"
import Smile5 from "../../../../images/05_VERY_HAPPY.png"
import Smile5Active from "../../../../images/05_VERY_HAPPY_CLICKED.png"

export class FormSmileRating extends Component {

    constructor(props) {
        super(props);
        const methods = ["displayFormField", "getProperImage"];
        methods.forEach(method => (this[method] = this[method].bind(this)));
        this.state = {
            fieldValue: props.fieldValue
        };
    }

    render() {

        const { fieldName } = this.props

        return (
            <div className="form-smile-rating">
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
        return (
            <div className="form-smile-rating__input">
                <img src={ this.getProperImage(value) } onClick={() => {
                    if ( !this.props.disabled ) {
                        this.setState({
                            fieldValue: value
                        })
                    }
                }} />
            </div>
        )
    }

    getProperImage( value ) {
        switch(value) {
            case 1:
                return 1 === parseInt(this.state.fieldValue) ? Smile1Active : Smile1
            case 2:
                return 2 === parseInt(this.state.fieldValue) ? Smile2Active : Smile2
            case 3:
                return 3 === parseInt(this.state.fieldValue) ? Smile3Active : Smile3
            case 4:
                return 4 === parseInt(this.state.fieldValue) ? Smile4Active : Smile4
            case 5:
                return 5 === parseInt(this.state.fieldValue) ? Smile5Active : Smile5
        }
    }
}

export default FormSmileRating
