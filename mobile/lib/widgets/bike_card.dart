import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/motorcycle.dart';
import '../theme/kadi_theme.dart';
import 'money_text.dart';

class BikeCard extends StatelessWidget {
  const BikeCard({super.key, required this.motorcycle, required this.onTap});

  final Motorcycle motorcycle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: KadiColors.card,
      borderRadius: BorderRadius.circular(8),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AspectRatio(
              aspectRatio: 4 / 3,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  Image.asset(
                    motorcycle.localAsset,
                    fit: BoxFit.contain,
                    errorBuilder: (_, __, ___) => const ColoredBox(
                      color: Color(0xFF0E0E10),
                      child: Center(child: Text('Sin foto')),
                    ),
                  ),
                  Positioned(
                    left: 10,
                    bottom: 10,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.7),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        '${motorcycle.displacement}CC',
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.w800,
                          fontSize: 11,
                          color: KadiColors.white,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    motorcycle.shortName,
                    style: GoogleFonts.barlowCondensed(
                      fontSize: 24,
                      fontWeight: FontWeight.w700,
                      color: KadiColors.white,
                    ),
                  ),
                  Text(
                    motorcycle.lineLabel.toUpperCase(),
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 0.8,
                      color: KadiColors.red,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    motorcycle.summary,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.inter(
                      fontSize: 13,
                      color: const Color(0xFFB0B0B0),
                      height: 1.35,
                    ),
                  ),
                  const SizedBox(height: 10),
                  MoneyText(motorcycle.quote.monthly, suffix: '/ mes'),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
