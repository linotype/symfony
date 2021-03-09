import { Controller } from "stimulus"

export default class extends Controller {

  initialize() {
    
    let block = this.element;
    let id = block.getAttribute("id");
    let data = linotype[id];
    
    console.log( { load: "block", id: id , data: data } )

  }

}