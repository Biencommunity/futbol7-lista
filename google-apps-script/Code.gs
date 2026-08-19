var MAX_PLAYERS = 16;
var SHEET_NAME = 'Jugadores';

function doGet() {
  return HtmlService.createHtmlOutputFromFile('Index')
    .setTitle('Futbol 7 - Lista de Jugadores')
    .addMetaTag('viewport', 'width=device-width, initial-scale=1');
}

function getSheet_() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName(SHEET_NAME);
  if (!sheet) {
    sheet = ss.insertSheet(SHEET_NAME);
    sheet.appendRow(['id', 'nombre', 'token', 'fecha_alta']);
  }
  return sheet;
}

function nextId_() {
  var props = PropertiesService.getScriptProperties();
  var id = Number(props.getProperty('NEXT_ID') || '1');
  props.setProperty('NEXT_ID', String(id + 1));
  return id;
}

function checkAdminPassword_(pw) {
  var real = PropertiesService.getScriptProperties().getProperty('ADMIN_PASSWORD') || '';
  return !!pw && real !== '' && pw === real;
}

function getState() {
  var sheet = getSheet_();
  var data = sheet.getDataRange().getValues();
  var players = [];
  for (var i = 1; i < data.length; i++) {
    if (data[i][0] === '' || data[i][0] === undefined) continue;
    players.push({ id: data[i][0], name: data[i][1] });
  }
  var props = PropertiesService.getScriptProperties();
  return {
    players: players,
    max: MAX_PLAYERS,
    date: props.getProperty('MATCH_DATE') || '',
    time: props.getProperty('MATCH_TIME') || '',
    notes: props.getProperty('MATCH_NOTES') || ''
  };
}

function addPlayer(name) {
  name = (name || '').toString().trim().slice(0, 60);
  if (!name) {
    throw new Error('Escribe tu nombre.');
  }
  var sheet = getSheet_();
  var count = sheet.getLastRow() - 1;
  if (count >= MAX_PLAYERS) {
    throw new Error('La lista ya esta completa (16 jugadores).');
  }
  var id = nextId_();
  var token = Utilities.getUuid();
  sheet.appendRow([id, name, token, new Date()]);
  return { id: id, token: token };
}

function deletePlayer(id, token, adminPassword) {
  var sheet = getSheet_();
  var data = sheet.getDataRange().getValues();
  var isAdmin = checkAdminPassword_(adminPassword);

  for (var i = 1; i < data.length; i++) {
    if (String(data[i][0]) === String(id)) {
      if (!isAdmin && (!token || data[i][2] !== token)) {
        throw new Error('Solo puedes borrar tu propia inscripcion.');
      }
      sheet.deleteRow(i + 1);
      return { deleted: id };
    }
  }
  throw new Error('Ese jugador ya no esta en la lista.');
}

function updateSettings(date, time, notes, adminPassword) {
  if (!checkAdminPassword_(adminPassword)) {
    throw new Error('Contrasena de administrador incorrecta.');
  }
  var props = PropertiesService.getScriptProperties();
  props.setProperty('MATCH_DATE', (date || '').toString());
  props.setProperty('MATCH_TIME', (time || '').toString());
  props.setProperty('MATCH_NOTES', (notes || '').toString().slice(0, 140));
  return { date: date, time: time, notes: notes };
}
