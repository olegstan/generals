import React, { Component } from 'react';
import ReactDOM from 'react-dom';

export default class Main extends Component
{
    render() {
        return (
            <div className="container">

            </div>
        );
    }
}

if (document.getElementById('example'))
{
    ReactDOM.render(<Main />, document.getElementById('example'));
}
