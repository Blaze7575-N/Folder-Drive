import axios from "axios";

export function httpGet(url)
{
    return axios.get(url,{
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }    
    })
    ;
}


