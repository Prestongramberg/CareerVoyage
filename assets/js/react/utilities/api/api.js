import {baseFetch, baseFetchPromise} from '../base-fetch/base-fetch'

export function get(url, body) {
    return baseFetch(url, {method: 'get', body})
}

export function post(url, body) {
    return baseFetch(url, {method: 'post', body})
}

export function postPromise(url, body) {
    return baseFetchPromise(url, {method: 'post', body})
}
