import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class KadiColors {
  static const ink = Color(0xFF111111);
  static const muted = Color(0xFF5E5E5E);
  static const line = Color(0xFFE6E6E6);
  static const paper = Color(0xFFF6F6F4);
  static const white = Color(0xFFFFFFFF);
  static const red = Color(0xFFE10600);
  static const redDeep = Color(0xFFB10500);
  static const bg = Color(0xFF070708);
  static const card = Color(0xFF161616);
  static const surface = Color(0xFF0C0C0E);
}

ThemeData buildKadiTheme() {
  final base = ThemeData(
    useMaterial3: true,
    brightness: Brightness.dark,
    scaffoldBackgroundColor: KadiColors.bg,
    colorScheme: const ColorScheme.dark(
      primary: KadiColors.red,
      secondary: KadiColors.redDeep,
      surface: KadiColors.surface,
      onPrimary: KadiColors.white,
      onSurface: KadiColors.white,
    ),
  );

  return base.copyWith(
    textTheme: GoogleFonts.interTextTheme(base.textTheme).apply(
      bodyColor: KadiColors.white,
      displayColor: KadiColors.white,
    ),
    appBarTheme: AppBarTheme(
      backgroundColor: KadiColors.bg.withValues(alpha: 0.94),
      elevation: 0,
      centerTitle: false,
      titleTextStyle: GoogleFonts.barlowCondensed(
        fontSize: 28,
        fontWeight: FontWeight.w700,
        color: KadiColors.white,
        letterSpacing: 0.5,
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: KadiColors.red,
        foregroundColor: KadiColors.white,
        elevation: 0,
        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
        shape: const StadiumBorder(),
        textStyle: GoogleFonts.inter(fontWeight: FontWeight.w800, fontSize: 14),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: KadiColors.white,
        side: const BorderSide(color: Color(0xFF3A3A3A)),
        shape: const StadiumBorder(),
      ),
    ),
    chipTheme: base.chipTheme.copyWith(
      backgroundColor: const Color(0xFF1A1A1C),
      selectedColor: KadiColors.red,
      labelStyle: GoogleFonts.inter(fontWeight: FontWeight.w700, fontSize: 12),
      side: BorderSide.none,
      shape: const StadiumBorder(),
    ),
  );
}
