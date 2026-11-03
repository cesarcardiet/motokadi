import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/motorcycle.dart';
import '../theme/kadi_theme.dart';
import '../widgets/color_swatches.dart';
import '../widgets/quote_box.dart';

class MotorcycleDetailScreen extends StatefulWidget {
  const MotorcycleDetailScreen({super.key, required this.motorcycle});

  final Motorcycle motorcycle;

  @override
  State<MotorcycleDetailScreen> createState() => _MotorcycleDetailScreenState();
}

class _MotorcycleDetailScreenState extends State<MotorcycleDetailScreen> {
  late String selectedColor;

  @override
  void initState() {
    super.initState();
    selectedColor = widget.motorcycle.colors.isNotEmpty
        ? widget.motorcycle.colors.first
        : 'Negro';
  }

  @override
  Widget build(BuildContext context) {
    final moto = widget.motorcycle;

    return Scaffold(
      backgroundColor: KadiColors.bg,
      appBar: AppBar(
        title: Text(moto.shortName.toUpperCase()),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 32),
        children: [
          Text(
            moto.lineLabel.toUpperCase(),
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              letterSpacing: 2,
              color: KadiColors.red,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            moto.name,
            style: GoogleFonts.barlowCondensed(
              fontSize: 40,
              height: 1,
              fontWeight: FontWeight.w700,
              color: KadiColors.white,
            ),
          ),
          const SizedBox(height: 14),
          Container(
            decoration: BoxDecoration(
              color: KadiColors.card,
              borderRadius: BorderRadius.circular(14),
            ),
            padding: const EdgeInsets.all(12),
            child: AspectRatio(
              aspectRatio: 4 / 3,
              child: Image.asset(
                moto.localAsset,
                fit: BoxFit.contain,
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            moto.summary,
            style: GoogleFonts.inter(
              fontSize: 15,
              height: 1.45,
              color: const Color(0xFFCFCFCF),
            ),
          ),
          const SizedBox(height: 22),
          Text(
            'COLORES',
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.4,
              color: const Color(0xFF9A9A9A),
            ),
          ),
          const SizedBox(height: 10),
          ColorSwatches(
            colors: moto.colors,
            selected: selectedColor,
            onSelected: (value) => setState(() => selectedColor = value),
          ),
          const SizedBox(height: 22),
          Text(
            'FICHA TÉCNICA',
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.4,
              color: const Color(0xFF9A9A9A),
            ),
          ),
          const SizedBox(height: 10),
          _SpecTable(rows: [
            ('Modelo', moto.name),
            ('Línea', moto.lineLabel),
            ('Cilindrada', '${moto.displacement} cc'),
            ('Color elegido', selectedColor),
            ('Plazo', '${moto.termMonths} meses'),
            ('Precio', '\$${moto.price.toStringAsFixed(2)} USD'),
            ('Inicial', '\$${moto.initialPayment.toStringAsFixed(2)} USD'),
            ('Cuota mensual', '\$${moto.quote.monthly.toStringAsFixed(2)} USD'),
          ]),
          const SizedBox(height: 18),
          QuoteBox(quote: moto.quote),
          const SizedBox(height: 18),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    backgroundColor: KadiColors.red,
                    content: Text(
                      'Solicitud de ${moto.shortName} · color $selectedColor (API lista en /api/v1).',
                    ),
                  ),
                );
              },
              child: const Text('Solicitar esta moto'),
            ),
          ),
        ],
      ),
    );
  }
}

class _SpecTable extends StatelessWidget {
  const _SpecTable({required this.rows});

  final List<(String, String)> rows;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF121214),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFF2A2A2A)),
      ),
      child: Column(
        children: [
          for (var i = 0; i < rows.length; i++)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                border: i == rows.length - 1
                    ? null
                    : const Border(bottom: BorderSide(color: Color(0xFF2A2A2A))),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      rows[i].$1,
                      style: GoogleFonts.inter(
                        color: const Color(0xFF9A9A9A),
                        fontSize: 13,
                      ),
                    ),
                  ),
                  Expanded(
                    child: Text(
                      rows[i].$2,
                      textAlign: TextAlign.right,
                      style: GoogleFonts.inter(
                        color: KadiColors.white,
                        fontWeight: FontWeight.w700,
                        fontSize: 13,
                      ),
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
