const music=new Audio("./sounds/23.mp3");

// Add a timer handle for the thank-you sentence
var thanksTimer = null;
  
$(".btn").click(function(){
    $(".btn").css("display","none");
    music.play();
    $(".hello").css("display","block");
    $(".hello").fadeOut(3000);
    setTimeout(getReady,3000);

    // Hide the thank-you sentence immediately if it's visible and clear pending timer
    $("#thanks").hide();
    clearTimeout(thanksTimer);
})

function getReady(){
   $(".ready").css("display","block");
   $(".ready").fadeOut(2000);
   setTimeout(main,2000);
}

function main(){
    $(".content").css("display","block");
    $(".comming-soon").addClass("hide");

    setTimeout(function(){
        $(".site-name").text("Food Frame")
    },2000);
  
    setTimeout(function(){
        $(".comming-soon").removeClass("hide");
       
    },2500);
}

// Helper: schedule automatic hide of #thanks after 20 seconds when it's visible
function scheduleThanksHide(){
    clearTimeout(thanksTimer);
    if($("#thanks").length && $("#thanks").is(":visible")){
        thanksTimer = setTimeout(function(){
            $("#thanks").fadeOut(400);
        }, 20000); // 20,000 ms = 20 seconds
    }
}

// Start the timer on page load if the sentence is visible
$(document).ready(function(){
    scheduleThanksHide();
});

