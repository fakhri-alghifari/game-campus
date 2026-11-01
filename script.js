function sendScoreToPHP(playerName, score, moves, timeSeconds) {
    const formData = new FormData();
    formData.append('player_name', playerName);
    formData.append('score', score);
    formData.append('moves', moves);
    formData.append('time_seconds', timeSeconds);

    fetch('save_score.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respon PHP:', data.message);
    })
    .catch(error => {
        console.error('Error sending score:', error);
    });
}