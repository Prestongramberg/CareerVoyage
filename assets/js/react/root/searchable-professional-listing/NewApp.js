import React from "react";
import AppSearchAPIConnector from "@elastic/search-ui-app-search-connector";
import { SearchProvider, Results, SearchBox } from "@elastic/react-search-ui";
import { Layout } from "@elastic/react-search-ui-views";

import "@elastic/react-search-ui-views/lib/styles/styles.css";
import ProfessionalSearchAPIConnector from "../../components/SearchUI/ProfessionalSearchAPIConnector";
import {PagingInfo, Result } from "@elastic/react-search-ui/es/containers";
import { MultiCheckboxFacet } from "@elastic/react-search-ui-views";
import { Facet } from "@elastic/react-search-ui";

//import ResultInfoView from "./ResultInfoView";

const PagingInfoView = ({ start, end }) => (
    <div className="paging-info">
        <strong>
            {start} - {end}
        </strong>
    </div>
);

const ResultInfoView = ({
                            className,
                            result,
                            onClickLink,
                            titleField,
                            urlField,
                            ...rest
                        }) => {

    console.log(result);

    return (
        <div className="paging-info">
            <strong>
                {result.raw}
            </strong>
        </div>
    );

    debugger;

};

const connector = new ProfessionalSearchAPIConnector();

/**
 * @see https://github.com/elastic/search-ui
 *
 * @return {*}
 * @constructor
 */
export default function App() {

    let result = {
        id: {
            raw: "This is the first example",
            // A snippet value contains a highlighted value. I.e., 'I <em>am</em> a raw
            // result'. These are always sanitized and safe to render as html.
            snippet: "This is the...",
            acres: "Any"
        },
        raw: "This is the first example",
        // A snippet value contains a highlighted value. I.e., 'I <em>am</em> a raw
        // result'. These are always sanitized and safe to render as html.
        snippet: "This is the...",
        acres: "Any"
    };

    return (
        <SearchProvider
            config={{
                apiConnector: connector,
                searchQuery: {
                    //disjunctiveFacets: ["acres"],
                    //disjunctiveFacetsAnalyticsTags: ["Ignore"],
                    facets: {
                        acres: {
                            type: "range",
                            ranges: [
                                { from: -1, name: "Any" },
                                { from: 0, to: 1000, name: "Small" },
                                { from: 1001, to: 100000, name: "Medium" },
                                { from: 100001, name: "Large" }
                            ]
                        }
                    }
                }
            }}
        >



            <div className="App">
                <Layout
                    header={<SearchBox />}
                    bodyContent={<Results titleField="title" urlField="nps_link" resultView={ResultInfoView}/>}
                    sideContent={<Facet field="acres" label="Acres" view={MultiCheckboxFacet} />}

                />
            </div>

            <PagingInfo view={PagingInfoView} />


        </SearchProvider>
    );
}