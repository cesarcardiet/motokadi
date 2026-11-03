import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/motorcycle.dart';
import '../theme/kadi_theme.dart';
import 'money_text.dart';

class QuoteBox extends StatelessWidget {
  const QuoteBox({super.key, required this.quote, this.rate = 1000});

  final MotorcycleQuote quote;
  final double rate;

  @override
  Widget build(BuildContext context) {
    final cells = [
      ('Precio', quote.price),
      ('Inicial', quote.initial),
      ('Saldo', quote.balance),
      ('Cuota', quote.monthly),
    ];

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFF121214),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFF2A2A2A)),
      ),
      child: Wrap(
        spacing: 18,
        runSpacing: 14,
        children: cells
            .map(
              (cell) => SizedBox(
                width: 140,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      cell.$1.toUpperCase(),
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.8,
                        color: const Color(0xFF9A9A9A),
                      ),
                    ),
                    const SizedBox(height: 4),
                    MoneyText(cell.$2, rate: rate),
                  ],
                ),
              ),
            )
            .toList(),
      ),
    );
  }
}
