import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import SuratMasukListScreen from '../screens/SuratMasukListScreen';
import SuratMasukDetailScreen from '../screens/SuratMasukDetailScreen';
import SuratMasukCreateScreen from '../screens/SuratMasukCreateScreen';

export type SuratMasukStackParamList = {
  SuratMasukList: undefined;
  SuratMasukDetail: { id: number };
  SuratMasukCreate: undefined;
};

const Stack = createNativeStackNavigator<SuratMasukStackParamList>();

export default function SuratMasukNavigator() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: false,
        animation: 'slide_from_right',
      }}
    >
      <Stack.Screen name="SuratMasukList" component={SuratMasukListScreen} />
      <Stack.Screen name="SuratMasukDetail" component={SuratMasukDetailScreen} />
      <Stack.Screen name="SuratMasukCreate" component={SuratMasukCreateScreen} />
    </Stack.Navigator>
  );
}
