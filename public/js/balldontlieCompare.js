jQuery(document).ready(function() {
    $('select').on('change', function() {
        var season = document.getElementById('season-selector').value;
        var players = document.getElementById('players-data').getAttribute('data');
        $('.td-data').html('0');
        statsRequest = $.ajax({
            type: "GET",
            url: "https://www.balldontlie.io/api/v1/season_averages?season=" + season + players,
            success: function (msg) {
                var result = msg.data;
                result.forEach(function (value) {
                    $('#g'+(value.player_id).toString()).html((value.games_played).toString());
                    $('#t'+(value.player_id).toString()).html((value.min).toString());
                    $('#pts'+(value.player_id).toString()).html((value.pts).toString());
                })
            },
        })
    })
});