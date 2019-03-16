function SingleClient() {
    this.xhr = new XMLHttpRequest();
    this.url = "http://localhost:8000";
    // this.url = "https://jsonplaceholder.typicode.com/posts";
}

SingleClient.prototype.getMessages = function(userId){
    this.xhr.open("GET", this.url + "/" + userId, true);
    this.xhr.setRequestHeader("Content-Type", "application/json");
    this.xhr.send(null);
    this.xhr.onreadystatechange = function () {
        if (this.xhr.readyState === 4 && this.xhr.status === 200) {
            var jsonObj = JSON.parse(this.xhr.responseText);
            alert(
                this.xhr.responseText
                );
        }
    }.bind(this);
}

function getOnline() {
    var singleClient = new SingleClient();
    singleClient.getMessages(1);
}
