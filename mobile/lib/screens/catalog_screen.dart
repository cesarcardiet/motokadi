import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../models/motorcycle.dart';
import '../theme/kadi_theme.dart';
import '../widgets/bike_card.dart';
import '../widgets/kadi_header.dart';
import 'motorcycle_detail_screen.dart';

class CatalogScreen extends StatefulWidget {
  const CatalogScreen({super.key, required this.motorcycles});

  final List<Motorcycle> motorcycles;

  @override
  State<CatalogScreen> createState() => _CatalogScreenState();
}

class _CatalogScreenState extends State<CatalogScreen> {
  String line = 'todas';

  List<String> get lines {
    final set = widget.motorcycles.map((m) => m.line).toSet().toList()..sort();
    return ['todas', ...set];
  }

  String labelFor(String value) {
    if (value == 'todas') return 'Todas';
    return widget.motorcycles
        .firstWhere((m) => m.line == value, orElse: () => widget.motorcycles.first)
        .lineLabel;
  }

  @override
  Widget build(BuildContext context) {
    final filtered = line == 'todas'
        ? widget.motorcycles
        : widget.motorcycles.where((m) => m.line == line).toList();

    return CustomScrollView(
      slivers: [
        SliverPadding(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
          sliver: SliverToBoxAdapter(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const KadiHeader(
                  kicker: 'Catálogo',
                  title: 'Motos Kadi',
                  subtitle: 'Mismas fotos, líneas y cuotas de la web.',
                ),
                const SizedBox(height: 16),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: lines.map((value) {
                      final on = line == value;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: Text(labelFor(value).toUpperCase()),
                          selected: on,
                          onSelected: (_) => setState(() => line = value),
                          labelStyle: GoogleFonts.inter(
                            fontWeight: FontWeight.w800,
                            fontSize: 11,
                            letterSpacing: 0.6,
                            color: on ? KadiColors.white : const Color(0xFFCFCFCF),
                          ),
                          selectedColor: KadiColors.red,
                          backgroundColor: const Color(0xFF161616),
                          side: BorderSide.none,
                          showCheckmark: false,
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),
        ),
        SliverPadding(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 32),
          sliver: SliverGrid(
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 1,
              mainAxisSpacing: 16,
              crossAxisSpacing: 16,
              childAspectRatio: 0.92,
            ),
            delegate: SliverChildBuilderDelegate(
              (context, index) {
                final moto = filtered[index];
                return BikeCard(
                  motorcycle: moto,
                  onTap: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) => MotorcycleDetailScreen(motorcycle: moto),
                      ),
                    );
                  },
                );
              },
              childCount: filtered.length,
            ),
          ),
        ),
      ],
    );
  }
}
