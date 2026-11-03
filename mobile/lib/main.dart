import 'package:flutter/material.dart';

import 'screens/home_shell.dart';
import 'theme/kadi_theme.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MotokadiApp());
}

class MotokadiApp extends StatelessWidget {
  const MotokadiApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Kadi Motors',
      debugShowCheckedModeBanner: false,
      theme: buildKadiTheme(),
      home: const HomeShell(),
    );
  }
}
