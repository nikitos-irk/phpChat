function SingleClient() {
    this.xhr = new XMLHttpRequest();
    this.url = "http://localhost:8000";
    // this.url = "https://jsonplaceholder.typicode.com/posts";
}

SingleClient.prototype.getMessages = function(){
    this.xhr.open("POST", this.url, true);
    this.xhr.setRequestHeader("Content-Type", "application/json");
    this.xhr.send(null);
    this.xhr.onreadystatechange = function () {
        alert("this.xhr.status");
        alert(this.xhr.status);
        if (this.xhr.readyState === 4 && this.xhr.status === 200) {
            var jsonObj = JSON.parse(this.xhr.responseText);
            alert(jsonObj.a);
        }
    }.bind(this);
}

function getOnline() {
    var singleClient = new SingleClient();
    singleClient.getMessages();
}
