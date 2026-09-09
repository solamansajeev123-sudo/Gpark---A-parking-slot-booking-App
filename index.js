const menuicon = document.getElementById("menu-icon");

menuicon.onclick = function() {
    const sidebar = document.querySelector("#sidebar");
    sidebar.style.display = "flex";
};


const closeicon = document.getElementById("close-icon");

closeicon.onclick = function() {
    const sidebar = document.querySelector("#sidebar");
    sidebar.style.display = "none";
};


const sidebarLinks = document.querySelectorAll("#sidebar a");

sidebarLinks.forEach(function(link) {
    link.addEventListener("click", function() {
        const sidebar = document.querySelector("#sidebar");
        sidebar.style.display = "none";
    });
});


/* Contact Us API */

const contactForm = document.getElementById("contact-form");

if (contactForm) {

    contactForm.addEventListener("submit", function(e) {

        e.preventDefault();

        const formData = new FormData(contactForm);

        fetch("backend/contact.php", {
            method: "POST",
            body: formData
        })

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                contactForm.innerHTML =
                    "<p>" + data.message + "</p>";

            } else {

                alert(data.message);

            }

        })

        .catch(error => {

            alert("Unable to send message.");
            console.error(error);

        });

    });

}



