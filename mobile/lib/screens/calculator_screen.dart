import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/motorcycle.dart';
import '../theme/kadi_theme.dart';
import '../widgets/kadi_header.dart';
import '../widgets/money_text.dart';
import '../widgets/quote_box.dart';
import 'motorcycle_detail_screen.dart';

class CalculatorScreen extends StatefulWidget {
  const CalculatorScreen({super.key, required this.motorcycles});

  final List<Motorcycle> motorcycles;

  @override
  State<CalculatorScreen> createState() => _CalculatorScreenState();
}

class _CalculatorScreenState extends State<CalculatorScreen> {
  int index = 0;

  Motorcycle get selected => widget.motorcycles[index];

  void _shift(int delta) {
    if (widget.motorcycles.isEmpty) return;
    setState(() {
      index = (index + delta) % widget.motorcycles.length;
      if (index < 0) index += widget.motorcycles.length;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (widget.motorcycles.isEmpty) {
      return const Center(child: CircularProgressIndicator(color: KadiColors.red));
    }

    final moto = selected;

    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
      children: [
        const KadiHeader(
          kicker: 'Financiamiento',
          title: 'Calculadora de cuotas',
          subtitle: 'Misma fórmula de la web: saldo entre 9 meses, sin redondear hacia arriba.',
        ),
        const SizedBox(height: 22),
        Row(
          children: [
            _NavArrow(icon: Icons.chevron_left, onTap: () => _shift(-1)),
            const SizedBox(width: 10),
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(
                  color: KadiColors.white,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int>(
                    value: index,
                    isExpanded: true,
                    dropdownColor: KadiColors.white,
                    style: GoogleFonts.inter(
                      color: KadiColors.ink,
                      fontWeight: FontWeight.w700,
                      fontSize: 15,
                    ),
                    items: [
                      for (var i = 0; i < widget.motorcycles.length; i++)
                        DropdownMenuItem(
                          value: i,
                          child: Text(widget.motorcycles[i].name),
                        ),
                    ],
                    onChanged: (value) {
                      if (value == null) return;
                      setState(() => index = value);
                    },
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),
            _NavArrow(icon: Icons.chevron_right, onTap: () => _shift(1)),
          ],
        ),
        const SizedBox(height: 18),
        Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            GestureDetector(
              onTap: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => MotorcycleDetailScreen(motorcycle: moto),
                  ),
                );
              },
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.asset(
                  moto.localAsset,
                  width: 88,
                  height: 72,
                  fit: BoxFit.contain,
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'CUOTA MENSUAL',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1.2,
                      color: const Color(0xFF9A9A9A),
                    ),
                  ),
                  MoneyText(moto.quote.monthly, large: true, suffix: '/ mes'),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        QuoteBox(quote: moto.quote),
        const SizedBox(height: 10),
        Text(
          'Tasa vigente: 1.000 Bs = 1 USD · Plazo ${moto.quote.months} meses',
          style: GoogleFonts.inter(fontSize: 13, color: const Color(0xFF9A9A9A)),
        ),
        const SizedBox(height: 18),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => MotorcycleDetailScreen(motorcycle: moto),
                ),
              );
            },
            child: const Text('Ver ficha / solicitar'),
          ),
        ),
      ],
    );
  }
}

class _NavArrow extends StatelessWidget {
  const _NavArrow({required this.icon, required this.onTap});

  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: const Color(0xFF161616),
      shape: const CircleBorder(),
      child: InkWell(
        customBorder: const CircleBorder(),
        onTap: onTap,
        child: SizedBox(
          width: 44,
          height: 44,
          child: Icon(icon, color: KadiColors.white),
        ),
      ),
    );
  }
}
