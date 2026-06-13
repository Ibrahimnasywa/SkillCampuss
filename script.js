// =========================
// SKILLCAMPUS JAVASCRIPT
// =========================


// Alert Welcome
window.addEventListener("load", function(){

    console.log("SkillCampus Loaded");

});



// =========================
// BUTTON ANIMATION
// =========================

const buttons = document.querySelectorAll("button");

buttons.forEach(btn => {

    btn.addEventListener("mouseenter", () => {

        btn.style.transform = "scale(1.05)";
        btn.style.transition = "0.3s";

    });

    btn.addEventListener("mouseleave", () => {

        btn.style.transform = "scale(1)";

    });

});



// =========================
// FORM VALIDATION
// =========================

const forms = document.querySelectorAll("form");

forms.forEach(form => {

    form.addEventListener("submit", function(e){

        const inputs = form.querySelectorAll("input");

        let valid = true;

        inputs.forEach(input => {

            if(input.value.trim() === ""){

                valid = false;

                input.style.border = "2px solid red";

            }else{

                input.style.border = "1px solid #ccc";

            }

        });

        if(!valid){

            e.preventDefault();

            alert("Semua field wajib diisi!");

        }

    });

});



// =========================
// AUTO CLOSE ALERT
// =========================

const alertBox = document.querySelector(".alert");

if(alertBox){

    setTimeout(() => {

        alertBox.style.display = "none";

    }, 3000);

}



// =========================
// SMOOTH SCROLL
// =========================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {

    anchor.addEventListener("click", function(e){

        e.preventDefault();

        document.querySelector(this.getAttribute("href"))
        .scrollIntoView({

            behavior: "smooth"

        });

    });

});



// =========================
// DARK MODE SIMPLE
// =========================

const darkBtn = document.getElementById("darkModeBtn");

if(darkBtn){

    darkBtn.addEventListener("click", () => {

        document.body.classList.toggle("dark-mode");

    });

}