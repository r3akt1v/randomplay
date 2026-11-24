async function fetchWeather() {
    const apiKey = '2decab8fcfc14d3a9e9ddc3dbc7927ea'; 
    const city = 'Saint Petersburg';
    const url = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric&lang=ru`;
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to load data');
        const data = await response.json();
        const temperature = data.main.temp;
        const description = data.weather[0].description;
        document.getElementById('weather-info').innerText = `Температура: ${temperature}°C, ${description}`;
    } catch {
        document.getElementById('weather-info').innerText = 'Failed to load weather';
    }
}
window.onload = fetchWeather;
