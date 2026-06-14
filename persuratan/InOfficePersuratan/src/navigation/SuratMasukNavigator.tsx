import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import SuratMasukListScreen from '../screens/SuratMasukListScreen';
import SuratMasukDetailScreen from '../screens/SuratMasukDetailScreen';
import SuratMasukCreateScreen from '../screens/SuratMasukCreateScreen';
import SuratKeluarListScreen from '../screens/SuratKeluarListScreen';
import SuratKeluarDetailScreen from '../screens/SuratKeluarDetailScreen';
import SuratKeluarCreateScreen from '../screens/SuratKeluarCreateScreen';

export type SuratMasukStackParamList = {
  SuratMasukList: undefined;
  SuratMasukDetail: { id: number };
  SuratMasukCreate: undefined;
  SuratKeluarList: undefined;
  SuratKeluarDetail: { id: number };
  SuratKeluarCreate: undefined;
};

const Stack = createNativeStackNavigator<SuratMasukStackParamList>();

export default function SuratMasukNavigator() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: false,
        animation: 'slide_from_right',
        gestureEnabled: true,
      }}
    >
      <Stack.Screen name="SuratMasukList" component={SuratMasukListScreen} />
      <Stack.Screen name="SuratMasukDetail" component={SuratMasukDetailScreen} />
      <Stack.Screen name="SuratMasukCreate" component={SuratMasukCreateScreen} />
      <Stack.Screen name="SuratKeluarList" component={SuratKeluarListScreen} />
      <Stack.Screen name="SuratKeluarDetail" component={SuratKeluarDetailScreen} />
      <Stack.Screen name="SuratKeluarCreate" component={SuratKeluarCreateScreen} />
    </Stack.Navigator>
  );
}
