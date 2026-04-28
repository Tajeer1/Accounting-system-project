import 'package:flutter_test/flutter_test.dart';

import 'package:eventplus_mobile/main.dart';

void main() {
  testWidgets('App boots without error', (WidgetTester tester) async {
    await tester.pumpWidget(const EventPlusApp());
    expect(find.byType(EventPlusApp), findsOneWidget);
  });
}
