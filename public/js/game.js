import Main from './screens/Main.js';
import App from './Base/App.js';

var app;
var currentStage;

window.addEventListener("resize", function()
{
    app.app.resize(window.innerWidth, window.innerHeight);
});

document.addEventListener('DOMContentLoaded', function()
{
    App.container = PIXI.autoDetectRenderer(window.innerWidth, window.innerHeight, {
            autoResize: true,
            resolution: devicePixelRatio
    });

    document.body.appendChild(App.container.view);


    animate();
    function animate()
    {
        let stage = App.getStage();
        if(stage)
        {
            App.container.render(stage);
        }

        requestAnimationFrame(animate);
    }

    // function onButtonDown()
    // {
    //     this.isdown = true;
    //     this.texture = textureButtonDown;
    //     this.alpha = 1;
    // }
    //
    // function onButtonUp()
    // {
    //     this.isdown = false;
    //
    //     if (this.isOver)
    //     {
    //         this.texture = textureButtonOver;
    //     }
    //     else
    //     {
    //         this.texture = textureButton;
    //     }
    // }
    //
    // function onButtonOver()
    // {
    //     this.isOver = true;
    //
    //     if (this.isdown)
    //     {
    //         return;
    //     }
    //
    //     this.texture = textureButtonOver;
    // }
    //
    // function onButtonOut()
    // {
    //     this.isOver = false;
    //
    //     if (this.isdown)
    //     {
    //         return;
    //     }
    //
    //     this.texture = textureButton;
    // }
});