import Stage from "../Base/Stage.js";
import Start from "./Main/buttons/Start.js";

export default class Main extends Stage
{
    getButtons()
    {
        return [
            new Start()
        ]
    }
}