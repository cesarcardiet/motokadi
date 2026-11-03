import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../theme/kadi_theme.dart';

class MoneyText extends StatelessWidget {
  const MoneyText(
    this.amount, {
    super.key,
    this.rate = 1000,
    this.large = false,
    this.suffix,
  });

  final double amount;
  final double rate;
  final bool large;
  final String? suffix;

  @override
  Widget build(BuildContext context) {
    final usd = '\$${amount.round().toString()} USD';
    final ves = 'Bs. ${(amount * rate).round()}';
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text.rich(
          TextSpan(
            children: [
              TextSpan(
                text: usd,
                style: GoogleFonts.inter(
                  fontWeight: FontWeight.w800,
                  fontSize: large ? 28 : 16,
                  color: KadiColors.white,
                ),
              ),
              if (suffix != null)
                TextSpan(
                  text: ' $suffix',
                  style: GoogleFonts.inter(
                    fontWeight: FontWeight.w600,
                    fontSize: large ? 16 : 13,
                    color: const Color(0xFF9A9A9A),
                  ),
                ),
            ],
          ),
        ),
        Text(
          ves,
          style: GoogleFonts.inter(
            fontSize: large ? 14 : 12,
            color: const Color(0xFF9A9A9A),
          ),
        ),
      ],
    );
  }
}
