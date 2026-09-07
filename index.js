const menuicon=document.getElementById("menu-icon");
menuicon.onclick=function(){
    const sidebar=document.querySelector("#sidebar");
    sidebar.style.display="flex"
}

const closeicon=document.getElementById("close-icon");
closeicon.onclick=function(){
    const sidebar=document.querySelector("#sidebar");
    sidebar.style.display="none"
}

const sidebarLinks=document.querySelectorAll("#sidebar a");
sidebarLinks.forEach(function(link){
    link.addEventListener("click",function(){
        const sidebar=document.querySelector("#sidebar");
        sidebar.style.display="none"
    });
});

const contactForm=document.getElementById("contact-form");
if(contactForm){
    contactForm.addEventListener("submit",function(e){
        e.preventDefault();
        contactForm.innerHTML="<p>Thanks — we'll get back to you shortly.</p>";
    });
}