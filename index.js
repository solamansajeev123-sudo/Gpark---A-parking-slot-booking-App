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