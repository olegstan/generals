export default class Button
{
    get x()
    {
        return 0;
    }

    get y()
    {
        return 0;
    }

    get width()
    {
        return 0;
    }

    get height()
    {
        return 0;
    }

    get image()
    {
        return '';
    }

    render(stage)
    {
        var image = PIXI.Texture.fromImage(this.image);
        var texture = new PIXI.Texture(image);
        var button = new PIXI.Sprite(texture, this.width, this.height);

        button.buttonMode = true;

        button.position.x = this.x;
        button.position.y = this.y;

        button.anchor.set(0.5);

        button.interactive = true;

        button
            .on('mousedown', this.mousedown)
            .on('touchstart', this.mousedown)
            .on('mouseup', this.mouseup)
            .on('touchend', this.touchend)
            .on('mouseupoutside', this.mouseupoutside)
            .on('touchendoutside', this.touchendoutside)
            .on('mouseover', this.mouseover)
            .on('mouseout', this.mouseout);

        stage.addChild(button);
    }

    mousedown()
    {

    }

    touchstart()
    {

    }

    mouseup()
    {

    }

    touchend()
    {

    }

    mouseupoutside()
    {

    }

    touchendoutside()
    {

    }

    mouseover()
    {

    }

    mouseout()
    {

    }

}