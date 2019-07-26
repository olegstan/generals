import Button from "./Button.js";


export default class Stage
{
    app = null;

    container = null;

    constructor(app)
    {
        this.app = app;
        this.init();
    }

    init()
    {
        this.container = new PIXI.Container();

        var image = PIXI.Texture﻿.fromImage('/images/bg.png');
        var texture = new PIXI.Texture(image);
        var background = new PIXI.extras.TilingSprite(texture, window.innerWidth, window.innerHeight);

        this.container.addChild(background);

        let buttons = this.getButtons();
        let length = buttons.length;
        for (var i = 0; i < length; i++)
        {
            buttons[i].render(this.container);
        }
    }

    /**
     *
     * @returns Button[]
     */
    getButtons()
    {
        return [];
    }

    render()
    {
        return this.container;
    }

    get()
    {

    }
}