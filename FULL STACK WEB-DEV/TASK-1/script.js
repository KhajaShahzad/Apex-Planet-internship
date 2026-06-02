function showMessage(){

    alert("Welcome To My Portfolio!");

}

document.getElementById("contactForm")
.addEventListener("submit", function(e){

    let email = document.getElementById("email").value;

    if(!email.includes("@")){

        alert("Please enter valid email");
        e.preventDefault();

    }
});