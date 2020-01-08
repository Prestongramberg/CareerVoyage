import React, { Component } from 'react';

const cb = 'form-choose-all-that-apply'

export class FormChooseAllThatApply extends Component {

    constructor(props) {
        super(props);
        this.state = {};
    }

    componentWillMount() {
        const fields = JSON.parse( this.props.fields )

        fields.forEach(field => {
            this.setState({
                [field.name]: field.value
            })
        })
    }

    render() {

        const fields = JSON.parse( this.props.fields )

        return (
            <div className={cb}>
                {fields.map(field => {

                    const active = 1 === parseInt( this.state[field.name] ) ? `${cb}__field--active` : ''

                    return (
                        <div className={`${cb}__field`} key={field.name} onClick={() => {
                            if ( !this.props.disabled ) {
                                this.setState({
                                    [field.name]: this.state[field.name] ? 0 : 1
                                })
                            }
                        }}>
                            <div className={`${cb}__image`}>
                                <img src={ active ? field.activeImage : field.image } />
                                <input type="hidden" name={field.name} value={this.state[field.name]} />
                            </div>
                            <div className={`${cb}__label`}>
                                { field.label }
                            </div>
                        </div>
                    )
                })}
            </div>
        );
    }
}

export default FormChooseAllThatApply
