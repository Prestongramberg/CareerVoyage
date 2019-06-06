'use strict';

import $ from 'jquery';
import MusicImportForm from "./MusicImportForm";

class BingoApp {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     */
    constructor($wrapper, globalEventDispatcher) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.render();
    }

    render() {
        this.$wrapper.html(BingoApp.markup());

        new MusicImportForm(this.$wrapper.find('.js-music-import-form'), this.globalEventDispatcher);

       /* new ScoreTopBar(this.$wrapper.find('.js-top-bar'), this.globalEventDispatcher, this.portal);
        new Scoreboard(this.$wrapper.find('.js-scoreboard'), this.globalEventDispatcher, this.portal);
        new ScoresTable(this.$wrapper.find('.js-scores-table'), this.globalEventDispatcher, this.portal);*/
    }

    static markup() {

        return `
        <div class="js-music-import-form"></div>
    `;
    }
}

export default BingoApp;