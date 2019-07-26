import Button from "../../../Base/Button.js";

export default class Start extends Button
{
    get x()
    {
        return (window.innerWidth / 2) - (this.width / 2);
    }

    get y()
    {
        return (window.innerHeight / 2) - (this.height / 2);
    }

    get width()
    {
        return 100;
    }

    get height()
    {
        return 100;
    }

    get image()
    {
        return '/images/button.png';
    }


}