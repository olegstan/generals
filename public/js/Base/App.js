import Main from "../screens/Main.js";
import Play from "../screens/Play.js";

export default class App
{
    static currenctStage = 'main';

    /**
     * TODO
     * @returns {*}
     */
    static getStage()
    {
        switch (App.currenctStage)
        {
            case 'main':
                return App.initStage(App.currenctStage, () => (new Main(App)).render());
            case 'play':
                return App.initStage(App.currenctStage, () => (new Play(App)).render());
            default:
                return false;
        }
    }

    static initStage(slug, item)
    {
        if(typeof App.stages[slug] === 'undefined' || App.stages[slug] === null)
        {
            if(typeof item === 'function')
            {
                App.stages[slug] = item();
            }else{
                App.stages[slug] = item;
            }

            return App.stages[slug];
        }else{
            return App.stages[slug];
        }
    }

    static stages = [];

    static container = null;
}