import '../models/motorcycle.dart';

/// Espejo del catálogo web (KadiCatalog) para modo offline / demo.
List<Motorcycle> seedCatalog() {
  const rows = <List<Object>>[
    [1, 'Kadi Furioso 250', 'furioso-250', 'enduro', 'Enduro', 250, 'Enduro de 250 cc, la referencia de trocha del catálogo.', 2150.0, 650.0, ['Negro', 'Rojo']],
    [2, 'Kadi Power 200', 'power-200', 'enduro', 'Enduro', 200, 'Enduro de 200 cc para salir de la ciudad sin dejar el diario.', 1800.0, 540.0, ['Negro', 'Blanco', 'Rojo']],
    [3, 'Kadi Playera 200', 'playera-200', 'atv', 'Cuatrimoto', 200, 'Cuatrimoto de 200 cc para arena, monte y uso familiar.', 2300.0, 690.0, ['Rojo', 'Negro', 'Verde']],
    [4, 'Kadi Classic 150', 'classic-150', 'sincronica', 'Sincrónica', 150, 'Sincrónica de 150 cc. Montos tomados de la tabla de ejemplo.', 950.0, 280.0, ['Negro', 'Rojo', 'Azul']],
    [5, 'Kadi Lovis 150', 'lovis-150', 'sincronica', 'Sincrónica', 150, 'Sincrónica de trabajo, 150 cc, pensada para el motorizado diario.', 1280.0, 380.0, ['Negro', 'Rojo', 'Gris']],
    [6, 'Kadi Kava 150', 'kava-150', 'sincronica', 'Sincrónica', 150, 'Estilo custom de 150 cc con presencia de calle.', 1290.0, 390.0, ['Negro', 'Cromado']],
    [7, 'Kadi Expo 150', 'expo-150', 'sincronica', 'Sincrónica', 150, 'Sincrónica económica de 150 cc para empezar.', 1190.0, 360.0, ['Negro', 'Azul', 'Rojo']],
    [8, 'Kadi KD 150', 'kd-150', 'sincronica', 'Sincrónica', 150, 'Deportiva de 150 cc con llanta ancha y look de calle.', 1340.0, 400.0, ['Negro', 'Rojo']],
    [9, 'Kadi Pionero 200', 'pionero-200', 'sincronica', 'Sincrónica', 200, 'Sincrónica de 200 cc para distancias más largas.', 1380.0, 420.0, ['Negro', 'Gris', 'Rojo']],
    [10, 'Kadi Terzo 250', 'terzo-250', 'automatica', 'Automática', 250, 'Maxi-scooter de 250 cc, cómodo para ciudad y carretera.', 1850.0, 560.0, ['Negro', 'Blanco', 'Gris']],
    [11, 'Kadi Cristal 150', 'cristal-150', 'automatica', 'Automática', 150, 'Scooter urbano de 150 cc, automático y fácil de manejar.', 1300.0, 390.0, ['Blanco', 'Negro', 'Rojo']],
    [12, 'Kadi Diamante 150', 'diamante-150', 'automatica', 'Automática', 150, 'Scooter de 150 cc. Montos tomados de la tabla de ejemplo.', 1400.0, 500.0, ['Negro', 'Rojo', 'Azul']],
    [13, 'Kadi Tundra 150', 'tundra-150', 'automatica', 'Automática', 150, 'Mini moto urbana de 150 cc. Montos tomados de la tabla de ejemplo.', 1420.0, 450.0, ['Negro', 'Blanco', 'Rojo']],
    [14, 'Kadi Carga 250', 'carga-250', 'carguero', 'Carguero', 250, 'Carguero de 250 cc con cabina techada.', 4130.0, 1240.0, ['Blanco', 'Rojo']],
    [15, 'Kadi Carga-D 300', 'carga-d-300', 'carguero', 'Carguero', 300, 'Carguero de 300 cc con cama de volteo hidráulico.', 4900.0, 1470.0, ['Blanco', 'Naranja']],
    [16, 'Kadi Carga-S 250', 'carga-s-250', 'carguero', 'Carguero', 250, 'Carguero básico de 250 cc con cama abierta.', 3600.0, 1080.0, ['Blanco', 'Azul']],
  ];

  return rows.map((row) {
    final price = row[7] as double;
    final initial = row[8] as double;
    final q = quoteFor(price: price, initial: initial);
    return Motorcycle(
      id: row[0] as int,
      name: row[1] as String,
      slug: row[2] as String,
      line: row[3] as String,
      lineLabel: row[4] as String,
      displacement: row[5] as int,
      summary: row[6] as String,
      price: price,
      initialPayment: initial,
      termMonths: 9,
      colors: List<String>.from(row[9] as List),
      quote: q,
      assetPath: 'assets/images/motos/${row[2]}.png',
    );
  }).toList();
}
