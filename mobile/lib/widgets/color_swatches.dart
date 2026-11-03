import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../theme/kadi_theme.dart';

Color colorFromName(String name) {
  final key = name.toLowerCase();
  if (key.contains('rojo')) return KadiColors.red;
  if (key.contains('negro')) return const Color(0xFF111111);
  if (key.contains('blanco')) return const Color(0xFFF2F2F2);
  if (key.contains('azul')) return const Color(0xFF1D4ED8);
  if (key.contains('verde')) return const Color(0xFF15803D);
  if (key.contains('gris')) return const Color(0xFF6B7280);
  if (key.contains('naranja')) return const Color(0xFFEA580C);
  if (key.contains('cromado') || key.contains('plata')) {
    return const Color(0xFFC0C0C0);
  }
  return const Color(0xFF444444);
}

class ColorSwatches extends StatelessWidget {
  const ColorSwatches({
    super.key,
    required this.colors,
    this.selected,
    this.onSelected,
  });

  final List<String> colors;
  final String? selected;
  final ValueChanged<String>? onSelected;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 10,
      runSpacing: 10,
      children: colors.map((name) {
        final selectedNow = selected == name;
        return GestureDetector(
          onTap: onSelected == null ? null : () => onSelected!(name),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            decoration: BoxDecoration(
              color: selectedNow ? const Color(0xFF1A0A0A) : const Color(0xFF161616),
              borderRadius: BorderRadius.circular(999),
              border: Border.all(
                color: selectedNow ? KadiColors.red : const Color(0xFF2A2A2A),
                width: selectedNow ? 1.5 : 1,
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 16,
                  height: 16,
                  decoration: BoxDecoration(
                    color: colorFromName(name),
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white24),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  name,
                  style: GoogleFonts.inter(
                    fontWeight: FontWeight.w700,
                    fontSize: 13,
                    color: KadiColors.white,
                  ),
                ),
              ],
            ),
          ),
        );
      }).toList(),
    );
  }
}
