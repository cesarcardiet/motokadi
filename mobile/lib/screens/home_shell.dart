import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../data/api_client.dart';
import '../models/motorcycle.dart';
import '../theme/kadi_theme.dart';
import 'calculator_screen.dart';
import 'catalog_screen.dart';

class HomeShell extends StatefulWidget {
  const HomeShell({super.key});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  final api = ApiClient();
  late Future<List<Motorcycle>> future;
  int tab = 0;

  @override
  void initState() {
    super.initState();
    future = api.fetchMotorcycles();
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Motorcycle>>(
      future: future,
      builder: (context, snapshot) {
        final motos = snapshot.data ?? const <Motorcycle>[];
        return Scaffold(
          backgroundColor: KadiColors.bg,
          appBar: AppBar(
            titleSpacing: 16,
            title: Row(
              children: [
                Image.asset('assets/images/logo-kadi.png', height: 36),
                const SizedBox(width: 12),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'KADI MOTORS',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 1.6,
                        color: KadiColors.red,
                      ),
                    ),
                    Text(
                      tab == 0 ? 'CALCULADORA' : 'CATÁLOGO',
                      style: GoogleFonts.barlowCondensed(
                        fontSize: 24,
                        fontWeight: FontWeight.w700,
                        color: KadiColors.white,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          body: snapshot.connectionState == ConnectionState.waiting && motos.isEmpty
              ? const Center(child: CircularProgressIndicator(color: KadiColors.red))
              : IndexedStack(
                  index: tab,
                  children: [
                    CalculatorScreen(motorcycles: motos),
                    CatalogScreen(motorcycles: motos),
                  ],
                ),
          bottomNavigationBar: NavigationBar(
            backgroundColor: KadiColors.surface,
            indicatorColor: const Color(0xFF1A0A0A),
            selectedIndex: tab,
            onDestinationSelected: (value) => setState(() => tab = value),
            destinations: const [
              NavigationDestination(
                icon: Icon(Icons.calculate_outlined),
                selectedIcon: Icon(Icons.calculate, color: KadiColors.red),
                label: 'Calculadora',
              ),
              NavigationDestination(
                icon: Icon(Icons.two_wheeler_outlined),
                selectedIcon: Icon(Icons.two_wheeler, color: KadiColors.red),
                label: 'Catálogo',
              ),
            ],
          ),
        );
      },
    );
  }
}
