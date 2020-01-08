import React, { Component } from 'react';
import InputRange from 'react-input-range';

import './styles.css';

const cb = 'form-range'

export class FormRange extends Component {

    constructor(props) {
        super(props);
        this.state = {
            fieldValue: parseInt( props.fieldValue || 0 )
        };
    }

    render() {

        const { disabled, fieldName } = this.props

        return (
            <div className={cb}>
                <InputRange
                    disabled={disabled}
                    maxValue={10}
                    minValue={0}
                    value={parseInt( this.state.fieldValue )}
                    onChange={value => this.setState({ 'fieldValue': value })} />
                <input type="hidden" name={fieldName} value={this.state.fieldValue} />
            </div>
        );
    }
}

export default FormRange
