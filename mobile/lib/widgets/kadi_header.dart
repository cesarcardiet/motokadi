import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../theme/kadi_theme.dart';

class KadiHeader extends StatelessWidget {
  const KadiHeader({
    super.key,
    required this.kicker,
    required this.title,
    this.subtitle,
  });

  final String kicker;
  final String title;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          kicker.toUpperCase(),
          style: GoogleFonts.inter(
            fontSize: 12,
            fontWeight: FontWeight.w800,
            letterSpacing: 2.2,
            color: KadiColors.red,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          title,
          style: GoogleFonts.barlowCondensed(
            fontSize: 36,
            height: 1,
            fontWeight: FontWeight.w700,
            color: KadiColors.white,
          ),
        ),
        if (subtitle != null) ...[
          const SizedBox(height: 8),
          Text(
            subtitle!,
            style: GoogleFonts.inter(
              fontSize: 15,
              height: 1.4,
              color: const Color(0xFFB0B0B0),
            ),
          ),
        ],
      ],
    );
  }
}
