class MotorcycleQuote {
  const MotorcycleQuote({
    required this.price,
    required this.initial,
    required this.balance,
    required this.monthly,
    required this.months,
  });

  final double price;
  final double initial;
  final double balance;
  final double monthly;
  final int months;

  factory MotorcycleQuote.fromJson(Map<String, dynamic> json) {
    return MotorcycleQuote(
      price: (json['price'] as num).toDouble(),
      initial: (json['initial'] as num).toDouble(),
      balance: (json['balance'] as num).toDouble(),
      monthly: (json['monthly'] as num).toDouble(),
      months: (json['months'] as num).toInt(),
    );
  }
}

class Motorcycle {
  const Motorcycle({
    required this.id,
    required this.name,
    required this.slug,
    required this.line,
    required this.lineLabel,
    required this.displacement,
    required this.summary,
    required this.price,
    required this.initialPayment,
    required this.termMonths,
    required this.colors,
    required this.quote,
    this.photoUrl,
    this.assetPath,
  });

  final int id;
  final String name;
  final String slug;
  final String line;
  final String lineLabel;
  final int displacement;
  final String summary;
  final double price;
  final double initialPayment;
  final int termMonths;
  final List<String> colors;
  final MotorcycleQuote quote;
  final String? photoUrl;
  final String? assetPath;

  String get shortName => name.replaceFirst('Kadi ', '');

  String get localAsset => assetPath ?? 'assets/images/motos/$slug.png';

  factory Motorcycle.fromJson(Map<String, dynamic> json) {
    final quoteJson = Map<String, dynamic>.from(json['quote'] as Map);
    return Motorcycle(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      line: json['line'] as String,
      lineLabel: json['line_label'] as String? ?? json['line'] as String,
      displacement: (json['displacement'] as num).toInt(),
      summary: json['summary'] as String? ?? '',
      price: (json['price'] as num).toDouble(),
      initialPayment: (json['initial_payment'] as num).toDouble(),
      termMonths: (json['term_months'] as num).toInt(),
      colors: (json['colors'] as List<dynamic>? ?? const [])
          .map((e) => e.toString())
          .toList(),
      quote: MotorcycleQuote.fromJson(quoteJson),
      photoUrl: json['photo_url'] as String?,
      assetPath: 'assets/images/motos/${json['slug']}.png',
    );
  }
}

/// Misma fórmula que InstallmentSimulator de la web.
MotorcycleQuote quoteFor({
  required double price,
  required double initial,
  int months = 9,
}) {
  final priceCents = (price * 100).round();
  final initialCents = (initial * 100).round();
  final balanceCents = priceCents - initialCents;
  final monthlyCents = balanceCents ~/ months;
  return MotorcycleQuote(
    price: priceCents / 100,
    initial: initialCents / 100,
    balance: balanceCents / 100,
    monthly: monthlyCents / 100,
    months: months,
  );
}
