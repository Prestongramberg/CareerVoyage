'use strict';

import $ from 'jquery';
import Routing from '../Routing';

class MusicImportForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {

        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;

        this.$wrapper.on(
            'submit',
            MusicImportForm._selectors.form,
            this.handleFormSubmit.bind(this)
        );

        this.loadForm();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            form: '.js-music-import-form',
        }
    }

    loadForm() {
        $.ajax({
            url: Routing.generate('music_import'),
        }).then(data => {
            this.$wrapper.html(data.formMarkup);
        })
    }

    /**
     * @param e
     */
    handleFormSubmit(e) {

        if(e.cancelable) {
            e.preventDefault();
        }

        const $form = $(e.currentTarget);
        let formData = new FormData($form.get(0));

        this._import(formData)
            .then((data) => {
                swal("Hooray!", "Well done, you have successfully imported the csv!", "success");
                this.globalEventDispatcher.publish(Settings.Events.CSV_SUCCESSFULLY_IMPORTED);
            }).catch((errorData) => {

            this.$wrapper.html(errorData.formMarkup);
        });
    }

    /**
     * @param data
     * @return {Promise<any>}
     * @private
     */
    _import(data) {
        return new Promise( (resolve, reject) => {
            const url = Routing.generate('music_import');

            $.ajax({
                url,
                method: 'POST',
                data: data,
                processData: false,
                contentType: false
            }).then((data, textStatus, jqXHR) => {
                resolve(data);
            }).catch((jqXHR) => {
                const errorData = JSON.parse(jqXHR.responseText);
                errorData.httpCode = jqXHR.status;
                reject(errorData);
            });
        });
    }
}

export default MusicImportForm;