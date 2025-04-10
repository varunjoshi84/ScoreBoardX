const express = require('express');
const app = express();
const path = require('path');

app.use(express.static('public'));

app.get('/login', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'auth', 'login.html'));
});

app.get('/register', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'auth', 'register.html'));
});
app.get('/admin/dashboard', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'admin', 'dashboard.html'));
});
app.get('/admin/users', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'admin', 'users.html'));
});
app.get('/admin/settings', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'admin', 'settings.html'));
});
app.get('/admin/matches', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'admin', 'managematches.html'));
});
app.get('/admin/matches/new', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'admin', 'addnewmatch.html'));
});
app.get('/admin/matches/:id/playing11', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'admin', 'setplaying11.html'));
});
app.get('/admin/matches/:id/score', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'admin', 'updatescores.html'));
});
app.get('/scores/:id', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'scores.html'));
});

app.listen(3000, () => console.log('Server running on http://localhost:3000'));
