let cookie = getCookie("comparison");
let html = document.getElementById("compare_nb_players");
if (cookie === undefined || cookie === '0') {
    html.innerHTML = "Vous n'avez sélectionné aucun joueur à comparer";
    html.hidden = true;
} else if (cookie === '1') {
    html.classList.add("text-white")
    html.innerHTML = "Vous avez sélectionné 1 joueur à comparer";
    html.href = "";
} else {
    html.classList.add("btn", "bg-white", "text-dark")
    html.innerHTML = "Comparer (" + cookie + " joueurs)";
}

function getCookie(cookieName) {
    let cookie = {};
    document.cookie.split(';').forEach(function(el) {
        let [key,value] = el.split('=');
        cookie[key.trim()] = value;
    })
    return cookie[cookieName];
}